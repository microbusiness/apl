<?php

namespace App\Controller;

namespace App\Controller;

use App\Service\TransService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;


class DefaultController extends AbstractController
{
    #[Route('/{_locale}',
        name: 'index',
        defaults: ['_locale' => 'en'],
        locale: 'en',
    )]
    public function index(Request $request, TransService $transService) : Response
    {
        //$tr = $transService->getTrans($request->getLocale());
        return $this->render('Default/'.$request->getLocale().'/index.html.twig');
    }

    #[Route(
        path: '/{_locale}/about',
        name: 'about',
        defaults: ['_locale' => 'en'],)]
    public function about(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/about.html.twig');
    }

    public function sendmail()
    {
        $request=$this->get('request_stack')->getCurrentRequest();
        $textMail=$request->request->get('text_mail');
        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
            ->setSubject('Почта с сайта моего портфолио')
            ->setFrom('noreplay@portfolio.cotrip.ru')
            ->setTo('mbusiness.iv@gmail.com')
            ->setBody(
                $textMail,
                'text/html'
            );
        $mailer->send($message);

        return $this->redirect($this->generateUrl('index'));
    }

    #[Route(
        path: '/{_locale}/experience',
        name: 'experience',
        defaults: ['_locale' => 'en'],)]
    public function experience(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/experience.html.twig');
    }

    #[Route(
        path: '/{_locale}/projects',
        name: 'projects',
        defaults: ['_locale' => 'en'],)]
    public function projects(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/projects.html.twig');
    }

    #[Route(
        path: '/{_locale}/technology',
        name: 'technology',
        defaults: ['_locale' => 'en'],)]
    public function technology(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/technology.html.twig');
    }

    #[Route(
        path: '/{_locale}/interest',
        name: 'interest',
        defaults: ['_locale' => 'en'],)]
    public function interest(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/interest.html.twig');
    }

    #[Route(
        path: '/{_locale}/hobby',
        name: 'hobby',
        defaults: ['_locale' => 'en'],)]
    public function hobby(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/hobby.html.twig');
    }

    #[Route(
        path: '/{_locale}/search',
        name: 'search',
        defaults: ['_locale' => 'en'],)]
    public function search(Request $request) : Response
    {
        return $this->render('Default/'.$request->getLocale().'/search.html.twig');
    }

    //{lng}/{ns}
    #[Route(
        path: '/api/translate/{lang}/{page}.json',
        name: 'translate',
        defaults: ['_locale' => 'en'],
        methods: ['POST'],
    )]
    public function translateJson(Request $request, TransService $transService, string $lang, string $page): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON in request body'], Response::HTTP_BAD_REQUEST);
        }

        $localPath = $this->getParameter('kernel.project_dir') . '/translations/' . $lang . '/' . $page . '.json';

        if (file_exists($localPath)) {
            $content = json_decode(file_get_contents($localPath), true);
            return new JsonResponse($content);
        }

        $result = $transService->translate($lang, $body['defaultTranslations']);

        return new JsonResponse($result['data']);
    }
}
