<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('surname', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Минимальное число символов - {{ limit }}',
                        'max' => 255,
                        'maxMessage' => 'Максимальное число символов - {{ limit }}',
                    ]),
                    new Regex([
                        'match' => true,
                        'pattern' => '/^[а-яё -]+$/ui',
                        'message' => 'Допустимо написание русскими буквами,пробелами и дефисами',
                    ]),
                ],
                'label' => 'Фамилия',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('name', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Минимальное число символов - {{ limit }}',
                        'max' => 255,
                        'maxMessage' => 'Максимальное число символов - {{ limit }}',
                    ]),
                    new Regex([
                        'match' => true,
                        'pattern' => '/^[а-яё -]+$/ui',
                        'message' => 'Допустимо написание русскими буквами,пробелами и дефисами',
                    ]),
                ],
                'label' => 'Имя',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('patronymic', null, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимальное число символов - {{ limit }}',
                        'max' => 255,
                    ]),
                    new Regex([
                        'match' => true,
                        'pattern' => '/^[а-яё -]+$/ui',
                        'message' => 'Допустимо написание русскими буквами,пробелами и дефисами',
                    ]),
                ],
                'label' => 'Отчество',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('phoneNumber', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимальное число символов {{ limit }}',
                        'max' => 20,
                    ]),
                    new Regex([
                        'match' => true,
                        'pattern' => '/^(\\+7|7|8)?[\\s\\-]?\\(?[489][0-9]{2}\\)?[\\s\\-]?[0-9]{3}[\\s\\-]?[0-9]{2}[\\s\\-]?[0-9]{2}$/',
                        'message' => 'Недопустимое написание номера телефона',
                    ]),
                ],
                'label' => 'Номер телефона',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Выберите пол',
                'choices' => [
                    'Мужской' => 'male',
                    'Женский' => 'female',
                ],
            ])
            ->add('address', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимальное число символов {{ limit }}',
                        'max' => 512,
                    ]),
                ],
                'label' => 'Адрес доставки',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Email([
                        'message' => 'Email введен не корректно!',
                    ]),
                    new Length([
                        'max' => 180,
                    ]),
                ],
                'label' => 'Электронная почта',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Необходимо ваше соглашение с использованием персональных данных',
                    ]),
                ],
                'label' => 'Согласен с использованием моих персональных данных',
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Пароль',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ваш пароль должен быть не менее {{ limit }} символов',
                        'max' => 4096,
                        'maxMessage' => 'Максимальное число символов - {{ limit }}',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
