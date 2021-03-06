<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\StatusEvent;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Timer;
use Knp\Component\Pager\PaginatorInterface;

class EventController extends AbstractController
{

    /**
     * @Route("manager/events", name="event_index")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $em = $this->getDoctrine()->getmanager()->getRepository(Event::class);
        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $events = $em->findBy([], ['date' => 'DESC']);
        } elseif (in_array("ROLE_MANAGER", $this->getUser()->getRoles())) {
            $events = $em->findBy(['society' => $this->getUser()->getSociety()->getId()], ['date' => 'DESC']);
        }

        $todayDate = new \DateTime();
        $weekEvents = [];
        $otherEvents = [];
        foreach ($events as $event) {
            if (($todayDate->diff($event->getDate())->format('%a') < 7) && $todayDate <= $event->getDate()) {
                $weekEvents[] = $event;
            } else {
                $otherEvents[] = $event;
            }
        }
        usort($weekEvents, function ($a, $b) {
            if ($a->getDate() == $b->getDate()) {
                return 0;
            }
            return ($a->getDate() < $b->getDate()) ? -1 : 1;
        });

        $result = $paginator->paginate(
            $otherEvents,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 6)
        );


        return $this->render('event/index.html.twig', [
            'events' => $result,
            'weekEvents' => $weekEvents
        ]);
    }

    /**
     * @Route("admin/event/add", name="event_add")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function add(Request $request): Response
    {
        $event = new Event();

        $timer = $this->getDoctrine()->getRepository(Timer::class)
            ->findOneBy([], ['id' => 'desc'], 1, 0);

        $statutEvent = $this->getDoctrine()->getRepository(StatusEvent::class)
            ->findOneBy([], [], 1, 0);

        $todayDate = new \DateTime();
        $logoPath = new File($this->getParameter('kernel.project_dir') . '/public/images/logos/defaultLogo.png');

        $event->setRoundMinutes($timer->getRoundMinutes());
        $event->setRoundSeconds($timer->getRoundSeconds());
        $event->setPauseMinutes($timer->getPauseMinutes());
        $event->setPauseSeconds($timer->getPauseSeconds());
        $event->setLogoFile($logoPath);
        $event->setDate($todayDate);

        $form = $this->createForm(EventType::class, $event, [
            'method' => Request::METHOD_POST,
            'status' => $statutEvent->getInPreparationState(),
            'statusFullState' => $statutEvent->getFullState(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre événement a été ajouté !'
            );

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/add.html.twig', [
            'formEvent' => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @Route("/manager/event/edit/{id}", name="event_edit", methods="GET|POST")
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function edit(Request $request, Event $event): Response
    {
        if (in_array("ROLE_MANAGER", $this->getUser()->getRoles())
        && $event->getSociety()->getId() !== $this->getUser()->getSociety()->getId()) {
            $this->addFlash(
                'danger',
                'Vous n\'avez pas accès à cet événement !'
            );
            return $this->redirectToRoute('event_index');
        }

        if ($event->getStatusEvent()->getState()>=$event->getStatusEvent()->getInProgressState()) {
            $this->addFlash(
                'danger',
                'L\'évènement n\'est plus modifiable !'
            );
            return $this->redirectToRoute('event_index');
        }

        $form = $this->createForm(EventType::class, $event, [
            'status' => $event->getStatusEvent()->getState(),
            'statusFullState' => $event->getStatusEvent()->getFullState(),
            'nbPlayers' => count($event->getPlayers())
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* check if the format has been modified,
               check if the number of registered players does not exceed
               the number of players of the new format
            */
            if (count($event->getPlayers()) < $event->getFormatEvent()->getNumberOfPlayers()
                && $event->getStatusEvent()->getState() == $event->getStatusEvent()->getFullState()) {
                // change event's status to registration status
                $statutEvent = $this->getDoctrine()->getManager()->getRepository(StatusEvent::class)
                    ->findOneBy(['state' => $event->getStatusEvent()->getRegistrationState()], []);

                $event->setStatusEvent($statutEvent);
            }

            if (count($event->getPlayers()) == $event->getFormatEvent()->getNumberOfPlayers()) {
                // change event's status to full status
                $statutEvent = $this->getDoctrine()->getManager()->getRepository(StatusEvent::class)
                    ->findOneBy(['state' => $event->getStatusEvent()->getFullState()], []);

                $event->setStatusEvent($statutEvent);
            }

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'success',
                'Votre événement a bien été modifié !'
            );

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'formEdit' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods="DELETE")
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($event);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre événement à été supprimé !'
            );
        }

        return $this->redirectToRoute('event_index');
    }

    /**
     * Return number of players present for event
     * @Route("/manager/event/{id}/present", requirements={"id"="\d+"}, methods={"POST"})
     * @param EventRepository $eventRepository
     * @param Event $event
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function getNumberPresentPlayers(EventRepository $eventRepository, Event $event, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new \Exception("This method can only be used with Ajax !");
        }
        $presentPlayers = $eventRepository->findPresentPlayer($event);
        $numberPresentPlayers = 0;
        if (count($presentPlayers) > 0) {
            $numberPresentPlayers = count($presentPlayers[0]->getPlayers());
        }

        return $this->json($numberPresentPlayers);
    }

    /**
     * @Route("/manager/event/{id}/start", name="event_start", requirements={"id"="\d+"}, methods={"GET"})
     * @param EventRepository $eventRepository
     * @param Event $event
     * @return Response
     */
    public function start(EventRepository $eventRepository, Event $event): Response
    {
        // assignment random speaker number to players
        $presentPlayers = $eventRepository->findPresentPlayer($event);
        if (empty($presentPlayers)) {
            $this->addFlash(
                'danger',
                'Impossible de lancer l\'évènement sans aucun participant présent !'
            );
            return $this->redirectToRoute('player', ['id'=> $event->getId()]);
        }
        $speakerNumbers = range(1, $event->getFormatEvent()->getNumberOfPlayers());

        foreach ($presentPlayers[0]->getPlayers() as $player) {
            $speakerNumber = array_rand($speakerNumbers);
            $player->setSpeakerNumber($speakerNumbers[$speakerNumber]);
            unset($speakerNumbers[$speakerNumber]);
        }

        // Modified event's status to In Progress
        $em = $this->getDoctrine()->getmanager()->getRepository(StatusEvent::class);
        $statusEvent = $em->findOneBy(['state' => $event->getStatusEvent()->getInProgressState()], []);
        $statusEvent->addEvent($event);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('dashboard_start', ['id' => $event->getId()]);
    }
}
