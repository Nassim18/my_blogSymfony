<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Event\ContactEvent;
//use App\Controller\ContactEvent;
use App\Security\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContactManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDisptacher;

    /**
     * @var MessageService
     */
    protected $messageService;

    public function __construct(
        EntityManagerInterface $em,
        MessageService $messageService,
        EventDispatcherInterface $eventDisptacher
    )
    {
        $this->em = $em;
        $this->messageService = $messageService;
        $this->eventDisptacher = $eventDisptacher;
    }

    /**
     * @param Contact $contact
     */
    public function sendContact(Contact $contact)
    {
        if ($contact instanceof Contact) {
            $this->em->persist($contact);
            $this->em->flush();

            $event = new ContactEvent($contact);
            $this->eventDisptacher->dispatch($event);

            $this->messageService->addSuccess('Your message has been sent successfully.');
        }
    }
}
