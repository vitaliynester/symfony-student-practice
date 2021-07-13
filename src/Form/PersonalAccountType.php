<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class PersonalAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phoneNumber',null,[
                'required'=>false,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов {{ limit }}',
                        'max'=>20,
                    ]),
                    new Regex([
                        'match'=>true,
                        'pattern'=>'/^(\\+7|7|8)?[\\s\\-]?\\(?[489][0-9]{2}\\)?[\\s\\-]?[0-9]{3}[\\s\\-]?[0-9]{2}[\\s\\-]?[0-9]{2}$/',
                        'message'=>'Недопустимое написание номера телефона'
                    ]),
                ],
                'label'=>'Номер телефона',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])

            ->add('gender', ChoiceType::class,[
                'required'=>false,
                'label'=>'Выберите пол',
                'choices'=>[
                    'Мужской'=>'male',
                    'Женский'=>'female',
                ],
            ])

            ->add('address',null,[
                'required'=>false,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов {{ limit }}',
                        'max'=>512,
                    ]),
                ],
                'label'=>'Адрес доставки',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])

            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ваш пароль должен быть не менее {{ limit }} символов',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
