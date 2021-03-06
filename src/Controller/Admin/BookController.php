<?php

declare(strict_types=1);

/*
 * This file is part of the library.
 */

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class BookController extends AbstractController
{
    public function new(EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(BookFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('admin/new_book.html.twig', [
            'bookForm' => $form->createView(),
        ]);
    }

    public function list(BookRepository $bookRepository)
    {
        $books = $bookRepository->findAll();

        return $this->render('admin/list.html.twig', [
            'books' => $books,
        ]);
    }

    public function edit(Book $book, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(BookFormType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var UploadedFile
             */
            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/bookImage';
                $originalFilename = \pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename) . '-' . \uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $book->setImageFilename($newFilename);
            }
            $book = $form->getData();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('admin/edit.html.twig', [
            'bookForm' => $form->createView(),
        ]);
    }
}
