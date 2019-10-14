<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends FOSRestController
{
    /**
     * Created By Nahla Sameh
     * Check if token access is authenticated
     * @Get(
     *     "/api/v1/isAuthenticated",
     *      name="isAuthenticated"
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function isAuthenticated(Request $request)
    {
        /* Get token from request header*/
        $token = $request->headers->get('Authorization', '');

        if ($this->_checkAuthToken($token)) {/* check if token is authenticated*/
            return new JsonResponse(array('isLogin' => true), 200);
        }
        return new JsonResponse(array(
            'isLogin' => false
        ), 401);
    }

    /**
     * Created By Nahla Sameh
     * Register clients
     * @Post(
     *     "/api/v1/register",
     *      name="register"
     * )
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function register(Request $request, ValidatorInterface $validator): JsonResponse
    {
        /* Get registeration data*/
        $email = $request->request->get('email', '');
        $username = $request->request->get('username', '');
        $phoneNumber = $request->request->get('phone_number', '');
        $password = $request->request->get('password', '');

        /* Check If User Email or Username exist */
        $clientExistResult = $this->_checkClientExist($email, $username);
        if (!$clientExistResult['isRegistered']) {
            return new JsonResponse($clientExistResult, 200);
        }

        /* Set Client Entity */
        $client = new Client();
        $client->setEmail($email);
        $client->setUsername($username);
        $client->setPhoneNumber($phoneNumber);
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT); /* hash password */
        $client->setPassword($passwordHashed);

        /* Validate Client Entity */
        $errors = $validator->validate($client);
        $customErrors = array();
        foreach ($errors as $error) {
            $customErrors[$error->getPropertyPath()] = $error->getMessage();
        }
        /* If Errors found it will return it to user interface*/
        if (count($customErrors) > 0) {
            return new JsonResponse(array(
                'isRegistered' => false,
                'errors' => $customErrors), 200);
        }

        /* If No Validation Errors client will be inserted*/
        $em = $this->getDoctrine()->getManager();
        $em->persist($client);
        $em->flush();
        $em->clear();

        return new JsonResponse(array(
            'isRegistered' => true,
            'message' => 'You signed up successfuly.'), 200);
    }

    /**
     * Created By Nahla Sameh
     * login clients
     * @Post(
     *     "/api/v1/login",
     *      name="login"
     * )
     * @param Request $request
     * @param ClientRepository $clientRepository
     * @return JsonResponse
     */
    public function login(Request $request, ClientRepository $clientRepository): JsonResponse
    {
        /* Remove Current client token  */
        $this->_removeCurrentToken();

        /* Get Login Data*/
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');

        /* Check If Client Username exist */
        $client = $clientRepository->findOneBy(['username' => $username]);
        if ($client === null) {
            return new JsonResponse(array('isLoggedIn' => false, 'message' => 'Username not exist'), 200);
        }

        /* Check password */
        $passwordVerified = password_verify($password, $client->getPassword());
        if ($passwordVerified) { /* Password verified*/
            $token = $this->_getAuthToken($username);
            return new JsonResponse(array('isLoggedIn' => true, 'username' => $username, 'token' => $token), 200);
        }

        /* Password not verified */
        return new JsonResponse(array('isLoggedIn' => false, 'message' => 'Password not right'), 200);
    }

    /**
     * Created By Nahla Sameh
     * Check if client email or username exist before
     * @param $email
     * @param $username
     * @return array
     */
    private function _checkClientExist($email, $username)
    {
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->getDoctrine()->getRepository(Client::class);

        /* Check If User Email exist */
        $clientExist = $clientRepository->findOneBy(['email' => $email]);
        if ($clientExist !== null) {
            return array(
                'isRegistered' => false,
                'message' => 'Email exist before.');
        }

        /* Check If User Username exist */
        $clientExist = $clientRepository->findOneBy(['username' => $username]);
        if ($clientExist !== null) {
            return array(
                'isRegistered' => false,
                'message' => 'Username exist before.');
        }
        /* Email and username not exist before*/
        return array(
            'isRegistered' => true
        );
    }

    /**
     * Created By Nahla Sameh
     * Create Authentication token
     * @param $username
     * @return string
     */
    private function _getAuthToken($username)
    {
        $session = new Session();
       //session->get('token');
        $token = md5(uniqid(rand(), true));
        $session->set('token', $token);
        $session->set('username', $username);
        return $token;
    }

    /**
     * Created By Nahla Sameh
     * Check if authentication token is right
     * @param $token
     * @return bool
     */
    private function _checkAuthToken($token)
    {
        $session = new Session();
        if ($session->get('token') === $token) {
            return true;
        }
        return false;
    }

    /**
     * Created By Nahla Sameh
     * Remove Current client token
     */
    private function _removeCurrentToken()
    {
        $session = new Session();
        $session->remove('token');
    }
}
