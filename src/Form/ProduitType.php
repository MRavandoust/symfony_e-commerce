<?php

namespace App\Form;

use App\Entity\Produit;
use Doctrine\DBAL\Types\SmallIntType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                new NotBlank(['message' => 'SAISIR UN TITRE !!!!']),
                    new Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => '5 min',
                        'maxMessage' => '100 max'
                    ])
                ]
            ])
            ->add('prix', MoneyType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'SAISIR STOCK !!!!']),
                ],
                'currency' => 'EUR',
                'required' => false,
            ])
            ->add('color' , ColorType::class)
            ->add('description', TextareaType::class)
            ->add('genre', ChoiceType::class, [
                'choices'  => [
                    'Homme' => "m",
                    'Femme' => "f",
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'data_class' => null,
            ])
            ->add('taille', ChoiceType::class, [
                'choices'  => [
                    'XS' => "xs",
                    'S' => "S",
                    'M' => "m",
                    'L' => "l",
                    'XL' => "xl",
                    'XXL' => "xxl",
                ],
            ])
            ->add('stock', IntegerType::class , [
                'constraints' => [
                new NotBlank(['message' => 'SAISIR STOCK !!!!']),
                ]
            ])
           // ->add('enregistrementAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
