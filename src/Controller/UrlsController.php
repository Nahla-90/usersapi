<?php

namespace App\Controller;

use App\Entity\Urls;
use App\Repository\ClientRepository;
use App\Repository\UrlsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Session\Session;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Patch;

/**
 * Class UrlsController
 * @package App\Controller
 */
class UrlsController extends FOSRestController
{

    /**
     * Created By Nahla Sameh
     * Get Client Urls
     * @GET(
     *     "/api/v1/urls",
     *      name="urls",
     * )
     * @param Request $request
     * @param UrlsRepository $urlsRepository
     * @return JsonResponse
     */
    public function urls(Request $request, UrlsRepository $urlsRepository): JsonResponse
    {
        /* Get Client Username */
        $clientUsername = $this->_getCurrentUsername();

        /* Get Table Data*/
        $data = array(
            'sortField' => $request->query->get('sortField', ''),
            'offset' => $request->query->get('offset', ''),
            'limit' => $request->query->get('limit', ''),
            'sortOrder' => $request->query->get('sortOrder', ''),
            'username' => $clientUsername
        );

        /* Get Client Urls*/
        $urls = $urlsRepository->getAll($data);
        $total = $urlsRepository->getAll($data, true)[0]['count'];
        return new JsonResponse(array('urls' => $urls, 'total' => $total), 200);
    }

    /**
     * Created By Nahla Sameh
     * Create Url related to current client
     * @Post(
     *     "/api/v1/urls",
     *      name="create"
     * )
     * @param Request $request
     * @param ClientRepository $clientRepository
     * @return JsonResponse
     */
    public function create(Request $request, ClientRepository $clientRepository)
    {
        /* Get Url text*/
        $text = $request->request->get('text', '');

        /* Get Client Username */
        $clientUsername = $this->_getCurrentUsername();
        $client = $clientRepository->findOneBy(['username' => $clientUsername]);

        /* Set Url Entity */
        $url = new Urls();
        $url->setText($text);
        $url->setClient($client);
        $em = $this->getDoctrine()->getManager();
        $em->persist($url);
        $em->flush();
        $em->clear();
        return new JsonResponse(array('success' => true), 200);
    }


    /**
     * Created By Nahla Sameh
     * Check Url Status
     * @Get(
     *     "/api/v1/urls/{id}",
     *      name="status"
     * )
     * @param UrlsRepository $urlsRepository
     * @param $id
     * @return JsonResponse
     */
    public function status(UrlsRepository $urlsRepository, $id)
    {
        /* Get Url Entity */
        $url = $urlsRepository->find($id);

        /* Check if Url Working */
        $url_headers = @get_headers($url->getText());
        if (!$url_headers || $url_headers[0] == 'HTTP/1.1 404 Not Found') {
            return new JsonResponse(array('status' => 'not found'), 200);
        } else {
            return new JsonResponse(array('status' => 'found'), 200);
        }
    }

    /**
     * Created By Nahla Sameh
     * Delete Specific Url
     * @Delete(
     *     "/api/v1/urls/{id}",
     *      name="delete"
     * )
     * @param UrlsRepository $urlsRepository
     * @param $id
     * @return JsonResponse
     */
    public function delete(UrlsRepository $urlsRepository, $id)
    {
        /* Get Url Entity */
        $url = $urlsRepository->find($id);

        /* Remove Url*/
        $em = $this->getDoctrine()->getManager();
        $em->remove($url);
        $em->flush();

        return new JsonResponse(array('success' => true), 200);
    }

    /**
     * Created By Nahla Sameh
     * Update Specific Entity
     * @Patch(
     *     "/api/v1/urls/{id}",
     *      name="update"
     * )
     * @param Request $request
     * @param UrlsRepository $urlsRepository
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, UrlsRepository $urlsRepository, $id)
    {
        /* Get Updated text*/
        $text = $request->request->get('text', '');

        /* Get Url Entity */
        $url = $urlsRepository->find($id);

        /* Set Url text*/
        $url->setText($text);
        $em = $this->getDoctrine()->getManager();
        $em->persist($url);
        $em->flush();
        return new JsonResponse(array('success' => true), 200);
    }

    /**
     * Created By Nahla Sameh
     * Get current client username
     * @return mixed
     */
    private function _getCurrentUsername()
    {
        $session = new Session();
        return $session->get('username');
    }
}
