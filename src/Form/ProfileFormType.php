<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label'       => 'Prénom',
                'constraints' => [new NotBlank(['message' => 'Le prénom est requis.'])],
            ])
            ->add('lastname', TextType::class, [
                'label'       => 'Nom',
                'constraints' => [new NotBlank(['message' => 'Le nom est requis.'])],
            ])
            ->add('dob', DateType::class, [
                'label'       => 'Date de naissance',
                'widget'      => 'single_text',
                'constraints' => [new NotBlank(['message' => 'La date de naissance est requise.'])],
            ])
            ->add('phone', TelType::class, [
                'label'      => 'Téléphone',
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('photo', TextType::class, [
                'label'      => 'Photo de profil (URL)',
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('favoriteColor', ColorType::class, [
                'label'    => 'Couleur préférée',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
