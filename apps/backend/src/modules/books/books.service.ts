import { PrismaClient, BookStatus } from '@prisma/client';
import { AppError } from '../../middleware/errorHandler.js';

const prisma = new PrismaClient();

export class BooksService {
  async getAll(userId: string, filters?: { status?: BookStatus; shelfId?: string }) {
    const where: any = { userId };

    if (filters?.status) {
      where.status = filters.status;
    }

    if (filters?.shelfId) {
      where.shelfBooks = {
        some: {
          shelfId: filters.shelfId,
        },
      };
    }

    const books = await prisma.book.findMany({
      where,
      orderBy: {
        updatedAt: 'desc',
      },
      include: {
        shelfBooks: {
          include: {
            shelf: {
              select: {
                id: true,
                name: true,
              },
            },
          },
        },
      },
    });

    return books;
  }

  async getById(bookId: string, userId: string) {
    const book = await prisma.book.findFirst({
      where: {
        id: bookId,
        userId,
      },
      include: {
        shelfBooks: {
          include: {
            shelf: {
              select: {
                id: true,
                name: true,
              },
            },
          },
        },
      },
    });

    if (!book) {
      throw new AppError(404, 'Book not found');
    }

    return book;
  }

  async create(userId: string, data: any) {
    const book = await prisma.book.create({
      data: {
        userId,
        ...data,
        startedAt: data.status === 'CURRENTLY_READING' ? new Date() : undefined,
        finishedAt: data.status === 'READ' ? new Date() : undefined,
      },
    });

    return book;
  }

  async update(bookId: string, userId: string, data: any) {
    // Verify ownership
    const existingBook = await prisma.book.findFirst({
      where: { id: bookId, userId },
    });

    if (!existingBook) {
      throw new AppError(404, 'Book not found');
    }

    // Update timestamps based on status changes
    const updateData: any = { ...data };

    if (data.status) {
      if (data.status === 'CURRENTLY_READING' && existingBook.status !== 'CURRENTLY_READING') {
        updateData.startedAt = new Date();
      }
      if (data.status === 'READ' && existingBook.status !== 'READ') {
        updateData.finishedAt = new Date();
      }
    }

    const book = await prisma.book.update({
      where: { id: bookId },
      data: updateData,
    });

    return book;
  }

  async delete(bookId: string, userId: string) {
    // Verify ownership
    const book = await prisma.book.findFirst({
      where: { id: bookId, userId },
    });

    if (!book) {
      throw new AppError(404, 'Book not found');
    }

    await prisma.book.delete({
      where: { id: bookId },
    });

    return { success: true };
  }

  async updateProgress(bookId: string, userId: string, currentPage: number) {
    // Verify ownership
    const book = await prisma.book.findFirst({
      where: { id: bookId, userId },
    });

    if (!book) {
      throw new AppError(404, 'Book not found');
    }

    // Validate progress
    if (book.pageCount && currentPage > book.pageCount) {
      throw new AppError(400, 'Current page cannot exceed total page count');
    }

    // Auto-update status if finished
    const updateData: any = { currentPage };
    if (book.pageCount && currentPage >= book.pageCount && book.status !== 'READ') {
      updateData.status = 'READ';
      updateData.finishedAt = new Date();
    }

    const updatedBook = await prisma.book.update({
      where: { id: bookId },
      data: updateData,
    });

    return updatedBook;
  }

  async updateStatus(bookId: string, userId: string, status: BookStatus) {
    // Verify ownership
    const book = await prisma.book.findFirst({
      where: { id: bookId, userId },
    });

    if (!book) {
      throw new AppError(404, 'Book not found');
    }

    const updateData: any = { status };

    // Update timestamps
    if (status === 'CURRENTLY_READING' && book.status !== 'CURRENTLY_READING') {
      updateData.startedAt = new Date();
    }
    if (status === 'READ' && book.status !== 'READ') {
      updateData.finishedAt = new Date();
    }

    const updatedBook = await prisma.book.update({
      where: { id: bookId },
      data: updateData,
    });

    return updatedBook;
  }
}
