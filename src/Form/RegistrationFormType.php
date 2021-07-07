<?php

namespace App\Form;

use App\Entity\User;
use phpDocumentor\Reflection\Type;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('surname',null,[
                'required'=>true,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов 255',
                        'max'=>255,
                    ]),
                ],
                'label'=>'Фамилия',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])
            ->add('name',null,[
                'required'=>true,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов 255',
                        'max'=>255,
                    ]),
                ],
                'label'=>'Имя',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])
            ->add('patronymic',null,[
                'required'=>false,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов 255',
                        'max'=>255,
                    ]),
                ],
                'label'=>'Отчество',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])
            ->add('phoneNumber',null,[
                'required'=>true,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов 20',
                        'max'=>20,
                    ]),
                ],
                'label'=>'Номер телефона',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])
            ->add('gender', ChoiceType::class,[
                'label'=>'Выберите пол',
                'choices'=>[
                    'Мужской'=>'Мужской',
                    'Женский'=>'Женский',
                ],
            ])
            ->add('address',null,[
                'required'=>true,
                'constraints'=>[
                    new Length([
                        'maxMessage'=>'Максимальное число символов 512',
                        'max'=>512,
                    ]),
                ],
                'label'=>'Адрес доставки',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])
            ->add('email',null,[
                'required'=>true,
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ваш пароль должен быть не менее {{ limit }} символов',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('birthday', BirthdayType::class, [
                'label' => 'День рождения',
                'required' => true,
                'widget'=>'single_text',
                'format'=>'yyyy-MM-dd'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        /*$resolver->setDefaults([
            'data_class' => User::class,
        ]);*/
    }
}
