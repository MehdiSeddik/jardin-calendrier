<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\EventImage;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine, string $projectDir)
    {
        $this->em = $doctrine->getManager();
        $this->userRepository = $doctrine->getRepository(User::class);
        $this->eventRepository = $doctrine->getRepository(Event::class);
        $this->eventImageRepository = $doctrine->getRepository(EventImage::class);
        $this->uploader = new UploadService($projectDir);
    }

    #[Route('/api/event/new', name: 'event.create', methods: ['POST'])]
    /**
     * @Route("/api/event/new", name="event.create", methods={"POST"})
     */
    public function newEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || empty($data['description']) || empty($data['start']) || empty($data['end'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing data'], Response::HTTP_BAD_REQUEST);
        }

        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $start = new \DateTime($data['start']);
        $event->setStart($start);

        $end = new \DateTime($data['end']);
        $event->setEnd($end);

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                // create file object


                $eventImage = new EventImage();
                $eventImage->setEvent($event);
                $eventImage->setBase64($image['base64']);
                $eventImage->setImagePath($this->uploader->upload($image['base64'], $image['filename']));
                $this->em->persist($eventImage);
            }
        }


        $this->em->persist($event);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Event created'], Response::HTTP_CREATED);
    }
    /**
     * @Route("/api/event/all", name="event.list", methods={"GET"})
     */
    public function listEvents(SerializerInterface $serializer): JsonResponse
    {
        $events = $this->eventRepository->findAll();

        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'start' => $event->getStart(),
                'end' => $event->getEnd(),
                'images' => $event->getEventImage()
            ];
        }

        return new JsonResponse($serializer->serialize($data, 'json', ['groups' => ['event:read']]), Response::HTTP_OK, [], true);
    }
}
