<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Hateoas\Configuration\Annotation as Hateoas;

class UserController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface $serializer,
    )
    { }

    #[Route('/api/users/{id}/customers', name: 'users_customers', methods: ['GET'])]
    public function getAllCustomersOfAUser(int $id, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $idCache = "getAllPhones-" . $page . "-" . $limit;

        $user = $this->userRepository->findOneById($id);
        $customersList = $cachePool->get($idCache, function (ItemInterface $item) use ($user, $page, $limit) {
            $item->tag("customerCache");

            return $this->customerRepository->findByUserWithPagination($user, $page, $limit);
        });
        $context = SerializationContext::create()->setGroups(['getAllCustomersOfAUser']);

        $jsonCustomersList = $this->serializer->serialize($customersList, 'json', $context);
        // return $this->json($jsonCustomersList, 200);
        return new JsonResponse($jsonCustomersList, Response::HTTP_OK, [], true);
    }

}
