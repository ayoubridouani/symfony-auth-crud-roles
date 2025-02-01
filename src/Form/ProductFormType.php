<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductFormType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Title <span class="text-danger">*</span>',
                'label_html' => true,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'empty_data' => '',
                'priority' => 99
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description <span class="text-danger">*</span>',
                'label_html' => true,
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'empty_data' => '',
                'priority' => 98
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price <span class="text-danger">*</span>',
                'label_html' => true,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'empty_data' => '0',
                'priority' => 97
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Upload Image (JPG, PNG)',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('attachmentFile', VichFileType::class, [
                'label' => 'Upload Attachment (PDF, XLSX)',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Attachment (.pdf, .xlsx)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);

        if($this->security->isGranted('ROLE_MANAGER')) {
            $builder->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => function ($user) {
                    return $user->getFirstname() . ' ' . $user->getLastname();
                },
                'label' => 'Owner <span class="text-danger">*</span>',
                'label_html' => true,
                'required' => true,
                'placeholder' => 'Select an owner',
                'attr' => ['class' => 'form-control'],
                'empty_data' => '',
                'priority' => 96
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => true,
            'method' => 'POST',
            'attr' => ['class' => 'custom-form-class'],
            'allow_file_upload' => true
        ]);
    }
}
