<?php

namespace Deeson\WardenBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Doctrine\UserManager;
use Deeson\WardenBundle\Document\UserDocument;

class UserController extends Controller {

  /**
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function listAction() {
    /** @var UserManager $userManager */
    $userManager = $this->get('fos_user.user_manager');

    $params = [
      'users' => $userManager->findUsers(),
    ];

    return $this->render('DeesonWardenBundle:User:index.html.twig', $params);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
   * @throws \Exception
   */
  public function newAction(Request $request) {
    /** @var UserManager $userManager */
    $userManager = $this->get('fos_user.user_manager');
    /** @var UserDocument $user */
    $user = $userManager->createUser();

    // @todo create a userFormType
    $formBuilder = $this->createFormBuilder($user, ['attr' => ['class' => 'box-body']]);
    $formBuilder->add('username', TextType::class, [
        'label' => 'Username: ',
        'attr' => [
          'class' => 'form-control',
        ],
      ]
    );
    $formBuilder->add('password', PasswordType::class, [
        'label' => 'Password: ',
        'attr' => [
          'class' => 'form-control',
        ],
      ]
    );
    //$formBuilder->add('groups');
    /*$groupOptions = [];
    foreach ($user->getGroups() as $group) {
      $groupOptions[$group->getId()] = $group->getName();
    }
    printf('<pre>%s</pre>', print_r($groupOptions, true));
    $formBuilder->add('groups', ChoiceType::class, [
      'choices'  => $groupOptions,
      //'expanded' => true,
      'multiple' => true,
      'required' => false,
      'attr' => array(
        'class' => 'form-checkbox'
      )
    ]);*/
    $formBuilder->add('save', SubmitType::class, [
        'attr' => [
          'class' => 'btn btn-danger',
        ],
      ]
    );

    $form = $formBuilder->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      try {
        // @todo user overridden manager/ create listener to handle these.
        $user->setEmail($user->getUsername());
        $user->setEnabled(true);
        $user->setCreatedDate(new \DateTime());
        $userManager->updateUser($user);

        $this->get('session')->getFlashBag()->add('notice', 'User created successfully');
        return $this->redirect($this->generateUrl('admin_user_list'));
      } catch (\MongoDuplicateKeyException $e) {
        $this->get('session')->getFlashBag()->add('error', 'Error creating user:' . $e->getMessage());
      }
    }

    $params = [
      'user' => $user,
      'form' => $form->createView(),
    ];

    return $this->render('DeesonWardenBundle:User:create.html.twig', $params);
  }

  /**
   * @param $id
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
   */
  public function editAction($id, Request $request) {
    /** @var UserManager $userManager */
    $userManager = $this->get('fos_user.user_manager');
    /** @var UserDocument $user */
    $user = $userManager->findUserBy(['id' => $id]);

    // @todo create a userFormType
    $formBuilder = $this->createFormBuilder($user, ['attr' => ['class' => 'box-body']]);
    $formBuilder->add('username', TextType::class, [
        'label' => 'Username: ',
        'attr' => [
          'class' => 'form-control',
        ],
      ]
    );
    $formBuilder->add('password', PasswordType::class, [
        'label' => 'Password: ',
        'attr' => [
          'class' => 'form-control',
        ],
      ]
    );
    $formBuilder->add('roles', ChoiceType::class, [
      'choices' => [
        'ROLE_ADMIN' => 'ROLE_ADMIN',
        //'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN'
      ],
      'choice_label' => function ($choice, $key, $value) {
        if ($choice === 'ROLE_USER') {
          return 'User';
        }
        if ($choice === 'ROLE_ADMIN') {
          return 'Admin';
        }
        if ($choice === 'ROLE_SUPER_ADMIN') {
          return 'Super Admin';
        }
        return $choice;
      },
      'expanded' => true,
      //'checked' => true,
      'multiple' => true,
      'required' => false,
      'attr' => array(
        'class' => 'form-checkbox'
      )
    ]);
    $formBuilder->add('groups');
    /*$groupOptions = [];
    foreach ($user->getGroups() as $group) {
      $groupOptions[$group->getId()] = $group->getName();
    }
    printf('<pre>%s</pre>', print_r($groupOptions, true));
    $formBuilder->add('groups', ChoiceType::class, [
      'choices'  => $groupOptions,
      //'expanded' => true,
      'multiple' => true,
      'required' => false,
      'attr' => array(
        'class' => 'form-checkbox'
      )
    ]);*/
    $formBuilder->add('enabled', CheckboxType::class, [
      'label' => 'Enabled: ',
      'required' => FALSE,
      'attr' => [
        'class' => 'form-checkbox',
      ],
    ]);
    $formBuilder->add('save', SubmitType::class, [
        'attr' => [
          'class' => 'btn btn-danger',
        ],
      ]
    );

    $form = $formBuilder->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // @todo user overridden manager/ update listener to handle these.
      $user->setEmail($user->getUsername());
      $user->setPlainPassword($user->getPassword());
      $userManager->updateUser($user);

      $this->get('session')->getFlashBag()->add('notice', 'User updated successfully');
      return $this->redirect($this->generateUrl('admin_user_list'));
    }

    $params = [
      'user' => $user,
      'form' => $form->createView(),
    ];

    return $this->render('DeesonWardenBundle:User:edit.html.twig', $params);
  }

  /**
   * @param $id
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
   */
  public function deleteAction($id, Request $request) {

    /** @var UserManager $userManager */
    $userManager = $this->get('fos_user.user_manager');
    /** @var UserDocument $user */
    $user = $userManager->findUserBy(['id' => $id]);

    $form = $this->createFormBuilder()
      ->add('Delete', SubmitType::class, [
        'attr' => ['class' => 'btn btn-danger'],
      ])
      ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $userManager->deleteUser($user);

      $this->get('session')->getFlashBag()->add('notice', 'The user [' . $user->getUsername() . '] has been deleted.');

      return $this->redirect($this->generateUrl('admin_user_list'));
    }

    $params = [
      'user' => $user,
      'form' => $form->createView(),
    ];
    return $this->render('DeesonWardenBundle:User:delete.html.twig', $params);
  }

  public function changePasswordAction() {
    die('cange password user');
  }

}
