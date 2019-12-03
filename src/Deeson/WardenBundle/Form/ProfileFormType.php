<?php

namespace Deeson\WardenBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends AbstractType {

   public function buildForm(FormBuilderInterface $builder, array $options) {
     $builder->remove('email');
     $builder->add('groups');
     /*$builder->add('groups', ChoiceType::class,
             array(
                 //'type'         => new GroupDocument(),
                 //'allow_add' => true,
                 //'options'      => array('data_class' => 'Deeson\WardenBundle\Document\GroupDocument'),
                 'choice_label' => 'name',
                 'by_reference' => false,

             ));*/
   }

   public function getParent() {
     return 'FOS\UserBundle\Form\Type\ProfileFormType';
   }

   public function getBlockPrefix() {
     return 'warden_user_profile';
   }

}
