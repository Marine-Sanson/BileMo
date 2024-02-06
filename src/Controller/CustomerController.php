<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Service\UserService;
use OpenApi\Annotations as OA;
use App\Service\CustomerService;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CustomerController extends AbstractController
{
    public function __construct(
        private readonly CustomerService $customerService,
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,        
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {}

    /**
     * Cette methode permet d'aller chercher le détail d'un utilisateur à partir son id
     * 
     * @Route("api/customers/{id}", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne le détail d'un téléphone",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     * @OA\Tag(name="Customer")
     *
     * @param Customer $customer Customer
     * 
     * @return JsonResponse
     */
    #[Route('/api/customers/{id}', name: 'detailCustomer', methods: ['GET'])]
    public function getDetailCustomer(Customer $customer): JsonResponse
    {

        $context = SerializationContext::create()->setGroups(['getDetailCustomer']);
        $jsonCustomer = $this->serializer->serialize($customer, 'json', $context);
        // return $this->json($jsonPhonesList, 200);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
        
    }

    /**
     * Cette methode permet de créer un nouvel utilisateur et de le lier à un client
     * 
     * @Route("api/customers/{id}", methods={"POST"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne le détail d'un téléphone",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     * @OA\Tag(name="Customer")
     *
     * @param Customer $customer Customer
     * 
     * @return JsonResponse
     */
    #[Route('/api/customers', name: 'newCustomer', methods: ['POST'])]
    public function newCustomer(Request $request): JsonResponse
    {

        $newCustomer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json');
        $content = $request->toArray();
        $userId = $content['userId'] ?? -1;
        $newCustomer->setUser($this->customerService->findCustomer($userId));

        $customer = $this->customerService->saveCustomer($newCustomer);

        $context = SerializationContext::create()->setGroups(['getDetailCustomer']);
        $jsonCustomer = $this->serializer->serialize($customer, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);

    }

    /**
     * Cette methode permet de supprimer un utilisateur à partir son id
     * 
     * @Route("api/customers/{id}", methods={"DELETE"})
     * 
     * @OA\Response(
     *     response=201,
     *     description="Supprime un utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     * @OA\Tag(name="Customer")
     *
     * @param Customer $customer Customer
     */
    #[Route('/api/customers/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer): JsonResponse
    {

        $this->customerService->deleteCustomer($customer);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

    }

}
