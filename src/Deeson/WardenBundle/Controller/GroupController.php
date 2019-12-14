<?php

namespace Deeson\WardenBundle\Controller;

use FOS\UserBundle\Event\FilterGroupResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseGroupEvent;
use FOS\UserBundle\Event\GroupEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\GroupManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupController extends Controller {

  private $eventDispatcher;

  private $formFactory;

  private $groupManager;

  public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, GroupManagerInterface $groupManager) {
    $this->eventDispatcher = $eventDispatcher;
    $this->formFactory = $formFactory;
    $this->groupManager = $groupManager;
  }

  /**
   * Show all groups.
   */
  public function listAction() {
    return $this->render('@FOSUser/Group/list.html.twig', [
      'groups' => $this->groupManager->findGroups(),
    ]);
  }

  /**
   * Edit one group, show the edit form.
   *
   * @param Request $request
   * @param int $id
   *
   * @return Response
   */
  public function editAction(Request $request, $id) {
    $group = $this->findGroupBy('id', $id);

    $event = new GetResponseGroupEvent($group, $request);
    $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_EDIT_INITIALIZE, $event);

    if (NULL !== $event->getResponse()) {
      return $event->getResponse();
    }

    $form = $this->formFactory->createForm();
    $form->setData($group);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $event = new FormEvent($form, $request);
      $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_EDIT_SUCCESS, $event);

      $this->groupManager->updateGroup($group);

      if (NULL === $response = $event->getResponse()) {
        $url = $this->generateUrl('admin_user_group_list');
        $response = new RedirectResponse($url);
      }

      $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_EDIT_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

      return $response;
    }

    return $this->render('@FOSUser/Group/edit.html.twig', [
      'form' => $form->createView(),
      'group_id' => $group->getId(),
    ]);
  }

  /**
   * Show the new form.
   *
   * @param Request $request
   *
   * @return Response
   */
  public function newAction(Request $request) {
    $group = $this->groupManager->createGroup('');

    $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_CREATE_INITIALIZE, new GroupEvent($group, $request));

    $form = $this->formFactory->createForm();
    $form->setData($group);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $event = new FormEvent($form, $request);
      $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_CREATE_SUCCESS, $event);

      $this->groupManager->updateGroup($group);

      if (NULL === $response = $event->getResponse()) {
        $url = $this->generateUrl('admin_user_group_list');
        $response = new RedirectResponse($url);
      }

      $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_CREATE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

      return $response;
    }

    return $this->render('@FOSUser/Group/new.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * Delete one group.
   *
   * @param Request $request
   * @param int $id
   *
   * @return RedirectResponse
   */
  public function deleteAction(Request $request, $id) {
    $group = $this->findGroupBy('id', $id);
    $this->groupManager->deleteGroup($group);

    $response = new RedirectResponse($this->generateUrl('admin_user_group_list'));

    $this->eventDispatcher->dispatch(FOSUserEvents::GROUP_DELETE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

    return $response;
  }

  /**
   * Find a group by a specific property.
   *
   * @param string $key  property name
   * @param mixed $value property value
   *
   * @return GroupInterface
   * @throws NotFoundHttpException if user does not exist
   *
   */
  protected function findGroupBy($key, $value) {
    if (!empty($value)) {
      $group = $this->groupManager->findGroupBy([$key => $value]);
    }

    if (empty($group)) {
      throw new NotFoundHttpException(sprintf('The group with "%s" does not exist for value "%s"', $key, $value));
    }

    return $group;
  }
}
