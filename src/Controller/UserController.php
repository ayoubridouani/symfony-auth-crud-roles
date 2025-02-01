<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

#[Route('/users', name: 'users.')]
class UserController extends AbstractController {
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        //$users = $em->getRepository(User::class)->findAll();

        $max_result = 10;
        $count = $em->getRepository(User::class)->count();
        $current_page = $request->query->get('page', 1);
        $last_page = intval(ceil($count/$max_result));
        $users = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($max_result)
            ->setFirstResult(($current_page-1)*$max_result)
            ->getQuery()
            ->getResult();
        return $this->render('user/index.html.twig', ['users' => $users, 'current_page' => $current_page, 'last_page' => $last_page, 'count' => $count, 'max_result' => $max_result]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, UploadHandler $uploadHandler): RedirectResponse|Response
    {
        if($request->isMethod(Request::METHOD_POST))
        {
            $token = $request->get('_token');
            if (!$this->isCsrfTokenValid('user' . $this->security->getUser()->getId(), $token)) {
                return new Response('Invalid CSRF token.', Response::HTTP_FORBIDDEN);
            }

            /*$existingUser = $em->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingUser) {
                return $this->json(['status' => 'error', 'message' => 'Username already exists.'], 400);
            }*/

            $imageFile = $request->files->get('imageFile');
            $attachmentFile = $request->files->get('attachmentFile');

            $user = new User();
            $user->setFirstname($request->get('firstname'));
            $user->setLastname($request->get('lastname'));
            $user->setEmail($request->get('email'));
            $user->setUsername($request->get('username'));
            $user->setRoles([$request->get('roles')]);
            $user->setPassword($request->get('password'));
            $user->setConfirmPassword($request->get('confirmPassword'));

            $errors = $validator->validate($user, null, ['Default', 'check-password']);

            if(count($errors) == 0) {
                $user->setImageFile($imageFile);
                $user->setAttachmentFile($attachmentFile);

                $uploadHandler->upload($user, 'imageFile');
                $uploadHandler->upload($user, 'attachmentFile');
            }

            $propertyPaths = [];
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $propertyPaths[] = $error->getPropertyPath();
                }
                return $this->render('user/create.html.twig', ['formData' => $user, 'errors' => $errors, 'propertyPaths' => $propertyPaths]);
            }
            
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute('users.index');
        }

        return $this->render('user/create.html.twig', ['errors' => [], 'propertyPaths' => []]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em, ValidatorInterface $validator, UploadHandler $uploadHandler): RedirectResponse|Response
    {
        if($request->isMethod(Request::METHOD_POST))
        {
            $token = $request->get('_token');
            if (!$this->isCsrfTokenValid('user' . $this->security->getUser()->getId(), $token)) {
                return new Response('Invalid CSRF token.', Response::HTTP_FORBIDDEN);
            }

            $user->setFirstname($request->get('firstname'));
            $user->setLastname($request->get('lastname'));
            $user->setEmail($request->get('email'));
            $user->setUsername($request->get('username'));
            $user->setRoles([$request->get('roles')]);

            if(!empty($request->get('password')) || !empty($request->get('password'))) {
                $user->setPassword($request->get('password'));
                $user->setConfirmPassword($request->get('confirmPassword'));
                $errors = $validator->validate($user, null, ['Default', 'check-password']);
            } else {
                $errors = $validator->validate($user, null, ['Default']);
            }

            if(count($errors) == 0) {
                $imageFile = $request->files->get('imageFile');
                if ($imageFile) {
                    $user->setImageFile($imageFile);
                    $uploadHandler->upload($user, 'imageFile');
                } else {
                    $imageFile_delete = $request->request->get('imageFile_delete');
                    if ($imageFile_delete) {
                        $uploadHandler->remove($user, 'imageFile');
                        $user->setImageName(null);
                    }
                }

                $attachmentFile = $request->files->get('attachmentFile');
                if ($attachmentFile) {
                    $user->setAttachmentFile($attachmentFile);
                    $uploadHandler->upload($user, 'attachmentFile');
                } else {
                    $attachmentFile_delete = $request->request->get('attachmentFile_delete');
                    if ($attachmentFile_delete) {
                        $uploadHandler->remove($user, 'attachmentFile');
                        $user->setAttachmentName(null);
                    }
                }
            }

            $propertyPaths = [];
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $propertyPaths[] = $error->getPropertyPath();
                }
                return $this->render('user/edit.html.twig', ['formData' => $user, 'errors' => $errors, 'propertyPaths' => $propertyPaths]);
            }

            $em->flush();

            return $this->redirectToRoute('users.index');
        }

        return $this->render('user/edit.html.twig', ['formData' => $user, 'errors' => [], 'propertyPaths' => []]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(User $user, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->get('_token');
        if (!$this->isCsrfTokenValid('user' . $this->security->getUser()->getId(), $token)) {
            return new Response('Invalid CSRF token.', Response::HTTP_FORBIDDEN);
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('users.index');
    }
}
