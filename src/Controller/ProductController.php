<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/products', name: 'products.')]
final class ProductController extends AbstractController {

    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route(path: '/', name: 'index')]
    public function index(EntityManagerInterface $entityManager, ProductRepository $productRepository, Connection $connection, DataTableFactory $dataTableFactory, Request $request): Response
    {
        //Method 1: Simple Repository Methods
        //$products = $entityManager->getRepository(Product::class)->findBy([], ['id' => 'desc'], 10, 10);

        //METHOD 2: Simple Repository Methods
        //$products = $productRepository->findBy([], ['id' => 'desc'], 10, 10);

        //Method 3: QueryBuilder
        /*$products = $entityManager
            //->getRepository(Product::class)->createQueryBuilder('p')
            ->createQueryBuilder()
            ->select('p')
            //->select('p.name', 'u.firstname', 'u.lastname')
            //->select('SUM(p.price) as sum')
            ->from(Product::class, 'p')
            ->where('p.deleted_at IS NULL')
            //->andWhere('p.owner = :owner_id')
            //->setParameter('owner_id', 10)
            ->leftJoin('p.owner', 'u')
            //->groupBy('p.owner')
            //->having('SUM(p.price) > 2800')
            ->orderBy('p.id', 'desc')
            //->orderBy('sum', 'desc')
            ->setMaxResults(10)
            ->setFirstResult(10)
            ->getQuery()
            ->getResult();*/

        // Method 4: Raw SQL Queries
        /*$sql = 'SELECT * FROM products where deleted_at is null AND id < :id ORDER BY id desc LIMIT 10 OFFSET 10';
        $stmt = $connection->executeQuery($sql, ['id' => '100']);
        $products = $stmt->fetchAllAssociative();*/

        // Method 5: DQL (Doctrine Query Language)
        /*$dql = 'SELECT p FROM App\Entity\Product p LEFT JOIN p.owner u WHERE p.deleted_at IS NULL ORDER BY p.id desc';
        $products = $entityManager
            ->createQuery($dql)
            ->setMaxResults(10)
            ->setFirstResult(10)
            ->getResult();*/

        // Method 6: Raw SQL Queries with ResultSetMapping
        /*$sql = 'SELECT p.* FROM products p left join users u on p.owner_id = u.id where p.deleted_at is null AND p.id < :id ORDER BY p.id desc LIMIT 10 OFFSET 10';
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('price', 'price');
        $rsm->addEntityResult(Product::class, 'p');
        $rsm->addEntityResult(User::class, 'u');

        $products = $entityManager
            ->createNativeQuery($sql, $rsm)
            ->setParameter('id', 100)
            ->getResult();*/

        // Method 7: PDO
        /*$pdo = new PDO('mysql:host=127.0.0.1;dbname=symfony_auth_roles_crud', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = 'SELECT p.* FROM products p left join users u on p.owner_id = u.id where p.deleted_at is null AND p.id < :id ORDER BY p.id desc LIMIT 10 OFFSET 10';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => 100]);
        $products = $stmt->fetchAll();*/

        // Build the Table
        //$products = $productRepository->findAll();
        //return $this->render('product/index.html.twig', ['products' => $products]);

        // Build the Table with DataTable
        $table = $dataTableFactory->create()
            ->add('id', NumberColumn::class, ['label' => 'ID'])
            ->add('name', TextColumn::class, ['label' => 'Name'])
            ->add('price', NumberColumn::class, ['label' => 'Price'])
            ->add('owner', TextColumn::class, ['label' => 'Owner', 'render' => function($value, $context) {
                return $context->getOwner()->getFirstname() . ' ' . $context->getOwner()->getLastname();
            }])
            ->add('created_at', DateTimeColumn::class, [
                'label' => 'Created At',
                'format' => 'Y-m-d H:i:s',
            ])
            ->add('actions', TextColumn::class, ['label' => 'Actions', 'render' => function($value, $context) {
                return '<a href="' . $this->urlGenerator->generate("products.edit", ['id' => $context->getId()]) . '">Edit</a>
                        <form method="post" action="' . $this->urlGenerator->generate("products.delete", ['id' => $context->getId()]) . '">
                            <input type="hidden" name="_token" value="' . $this->csrfTokenManager->getToken('delete' . $context->getId()) . '">
                            <button type="submit">Delete</button>
                        </form>';
            }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Product::class,
                'query' => function (QueryBuilder $builder) {
                    $builder->select('p')
                        ->from(Product::class, 'p')
                        ->orderBy('p.id', 'DESC');
                }
            ])
            ->handleRequest($request);

        // Check if the request is an AJAX callback
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('product/index.html.twig', ['datatable' => $table]);
    }

    #[Route(path: '/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Product();
        $form = $this->createForm(ProductFormType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('products.index');
        }

        return $this->render('product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Product $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductFormType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('products.index');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Product $post, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('delete' . $post->getId(), $token))) {
            return new Response('Invalid CSRF token.', Response::HTTP_FORBIDDEN);
        }

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('products.index');
    }
}
