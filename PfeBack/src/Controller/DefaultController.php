<?php

namespace App\Controller;



use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;    
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordDecoderInterface;
use App\Repository\UserRepository;

class DefaultController extends AbstractController
{   
    private $passwordEncoder;
    private $passwordDecoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, UserPasswordEncoderInterface $passwordDecoder)
     {
         $this->passwordEncoder = $passwordEncoder;
         $this->passwordDecoder=$passwordDecoder;   
     }
      /**
     * @Route("/api/add", name="add", methods={"POST"})
     */
    public function addUsers(Request $request, EntityManagerInterface $em){
        $request = Request::createFromGlobals();
        $jsonRecu= $request->getContent();
      
       try{ 
        $donnees =json_decode($jsonRecu);
        $user=new User();
        $user->setEmail($donnees->email);
        $user->setCin($donnees->cin);
        $user->setNom($donnees->nom);
        $user->setPrenom($donnees->prenom);
        $user->setPassword($this->passwordEncoder->encodePassword(
                         $user,
                         $donnees->password
                    ));
                   
        
        
        $em->persist($user);
        $em->flush();
        return new Response('ok', 201);      
        }
        catch (NotEncodableValueException $e)
        {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }

}
 
    public function getUsers2(UserRepository $usersRepo) 
    {    
        $users= $usersRepo->findAll();
        
        $encoders = [new JsonEncoder()];
        $normalizers =[new ObjectNormalizer()];
        $serializer =new Serializer($normalizers, $encoders);
        $jsonContent =$serializer->serialize($users, 'json', ['circular_reference_handler' => function($object){
            return $object->getId();
        }]);

        $response = new Response($jsonContent);

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        
        return $response;
    }


}

