import { PrismaClient } from '@prisma/client';
import { AppError } from '../../middleware/errorHandler.js';

const prisma = new PrismaClient();

export class ShelvesService {
  async getAll(userId: string) {
    const shelves = await prisma.shelf.findMany({
      where: { userId },
      orderBy: [{ isDefault: 'desc' }, { sortOrder: 'asc' }, { createdAt: 'asc' }],
      include: {
        _count: {
          select: { shelfBooks: true },
        },
      },
    });

    return shelves;
  }

  async getById(shelfId: string, userId: string) {
    const shelf = await prisma.shelf.findFirst({
      where: {
        id: shelfId,
        userId,
      },
      include: {
        shelfBooks: {
          include: {
            book: true,
          },
          orderBy: {
            addedAt: 'desc',
          },
        },
      },
    });

    if (!shelf) {
      throw new AppError(404, 'Shelf not found');
    }

    return shelf;
  }

  async create(userId: string, data: { name: string; description?: string; sortOrder?: number }) {
    // Check if shelf with same name already exists
    const existing = await prisma.shelf.findUnique({
      where: {
        userId_name: {
          userId,
          name: data.name,
        },
      },
    });

    if (existing) {
      throw new AppError(400, 'Shelf with this name already exists');
    }

    const shelf = await prisma.shelf.create({
      data: {
        userId,
        name: data.name,
        description: data.description,
        sortOrder: data.sortOrder || 0,
      },
    });

    return shelf;
  }

  async update(
    shelfId: string,
    userId: string,
    data: { name?: string; description?: string; sortOrder?: number }
  ) {
    // Verify ownership
    const shelf = await prisma.shelf.findFirst({
      where: { id: shelfId, userId },
    });

    if (!shelf) {
      throw new AppError(404, 'Shelf not found');
    }

    // Prevent updating default shelves' names
    if (shelf.isDefault && data.name && data.name !== shelf.name) {
      throw new AppError(400, 'Cannot rename default shelves');
    }

    // Check if new name conflicts with existing shelf
    if (data.name && data.name !== shelf.name) {
      const existing = await prisma.shelf.findUnique({
        where: {
          userId_name: {
            userId,
            name: data.name,
          },
        },
      });

      if (existing) {
        throw new AppError(400, 'Shelf with this name already exists');
      }
    }

    const updatedShelf = await prisma.shelf.update({
      where: { id: shelfId },
      data,
    });

    return updatedShelf;
  }

  async delete(shelfId: string, userId: string) {
    // Verify ownership
    const shelf = await prisma.shelf.findFirst({
      where: { id: shelfId, userId },
    });

    if (!shelf) {
      throw new AppError(404, 'Shelf not found');
    }

    // Prevent deleting default shelves
    if (shelf.isDefault) {
      throw new AppError(400, 'Cannot delete default shelves');
    }

    await prisma.shelf.delete({
      where: { id: shelfId },
    });

    return { success: true };
  }

  async addBook(shelfId: string, userId: string, bookId: string) {
    // Verify shelf ownership
    const shelf = await prisma.shelf.findFirst({
      where: { id: shelfId, userId },
    });

    if (!shelf) {
      throw new AppError(404, 'Shelf not found');
    }

    // Verify book ownership
    const book = await prisma.book.findFirst({
      where: { id: bookId, userId },
    });

    if (!book) {
      throw new AppError(404, 'Book not found');
    }

    // Check if book is already on shelf
    const existing = await prisma.shelfBook.findUnique({
      where: {
        shelfId_bookId: {
          shelfId,
          bookId,
        },
      },
    });

    if (existing) {
      throw new AppError(400, 'Book is already on this shelf');
    }

    const shelfBook = await prisma.shelfBook.create({
      data: {
        shelfId,
        bookId,
      },
      include: {
        book: true,
      },
    });

    return shelfBook;
  }

  async removeBook(shelfId: string, userId: string, bookId: string) {
    // Verify shelf ownership
    const shelf = await prisma.shelf.findFirst({
      where: { id: shelfId, userId },
    });

    if (!shelf) {
      throw new AppError(404, 'Shelf not found');
    }

    // Verify book is on shelf
    const shelfBook = await prisma.shelfBook.findUnique({
      where: {
        shelfId_bookId: {
          shelfId,
          bookId,
        },
      },
    });

    if (!shelfBook) {
      throw new AppError(404, 'Book not found on this shelf');
    }

    await prisma.shelfBook.delete({
      where: {
        shelfId_bookId: {
          shelfId,
          bookId,
        },
      },
    });

    return { success: true };
  }
}
