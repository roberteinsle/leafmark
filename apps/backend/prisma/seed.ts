import { PrismaClient, BookStatus } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  console.log('🌱 Starting database seed...');

  // Create test user
  const hashedPassword = await bcrypt.hash('password123', 10);

  const user = await prisma.user.upsert({
    where: { email: 'robert@einsle.com' },
    update: {},
    create: {
      email: 'robert@einsle.com',
      password: hashedPassword,
      name: 'Robert Einsle',
      language: 'de',
    },
  });

  console.log('✅ Created user:', user.email);

  // Create default shelves
  const shelves = await Promise.all([
    prisma.shelf.upsert({
      where: { userId_name: { userId: user.id, name: 'Want to Read' } },
      update: {},
      create: {
        userId: user.id,
        name: 'Want to Read',
        description: 'Books I want to read',
        isDefault: true,
        sortOrder: 1,
      },
    }),
    prisma.shelf.upsert({
      where: { userId_name: { userId: user.id, name: 'Currently Reading' } },
      update: {},
      create: {
        userId: user.id,
        name: 'Currently Reading',
        description: 'Books I am currently reading',
        isDefault: true,
        sortOrder: 2,
      },
    }),
    prisma.shelf.upsert({
      where: { userId_name: { userId: user.id, name: 'Read' } },
      update: {},
      create: {
        userId: user.id,
        name: 'Read',
        description: 'Books I have finished reading',
        isDefault: true,
        sortOrder: 3,
      },
    }),
  ]);

  console.log('✅ Created default shelves:', shelves.length);

  // Create sample books
  const book1 = await prisma.book.create({
    data: {
      userId: user.id,
      title: 'The Pragmatic Programmer',
      author: 'David Thomas, Andrew Hunt',
      isbn13: '9780135957059',
      pageCount: 352,
      status: BookStatus.CURRENTLY_READING,
      currentPage: 120,
      publisher: 'Addison-Wesley',
      publishedDate: '2019-09-13',
      description: 'A classic guide to software development and best practices.',
    },
  });

  const book2 = await prisma.book.create({
    data: {
      userId: user.id,
      title: 'Clean Code',
      author: 'Robert C. Martin',
      isbn13: '9780132350884',
      pageCount: 464,
      status: BookStatus.READ,
      currentPage: 464,
      publisher: 'Prentice Hall',
      publishedDate: '2008-08-01',
      description: 'A handbook of agile software craftsmanship.',
      finishedAt: new Date('2024-01-15'),
    },
  });

  const book3 = await prisma.book.create({
    data: {
      userId: user.id,
      title: 'Design Patterns',
      author: 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
      isbn13: '9780201633610',
      pageCount: 395,
      status: BookStatus.WANT_TO_READ,
      publisher: 'Addison-Wesley',
      publishedDate: '1994-10-31',
      description: 'Elements of Reusable Object-Oriented Software.',
    },
  });

  console.log('✅ Created sample books:', 3);

  // Add books to shelves
  await prisma.shelfBook.createMany({
    data: [
      { shelfId: shelves[1].id, bookId: book1.id }, // Currently Reading
      { shelfId: shelves[2].id, bookId: book2.id }, // Read
      { shelfId: shelves[0].id, bookId: book3.id }, // Want to Read
    ],
  });

  console.log('✅ Added books to shelves');

  console.log('🎉 Seed completed successfully!');
  console.log(`
📚 Test credentials:
   Email: robert@einsle.com
   Password: password123
  `);
}

main()
  .catch((e) => {
    console.error('❌ Seed failed:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
