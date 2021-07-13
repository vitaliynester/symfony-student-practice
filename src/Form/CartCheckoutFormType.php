<?php

namespace App\Form;

//use MongoDB\BSON\Regex;
use App\Service\ReferenceApi;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Type;

class CartCheckoutFormType extends AbstractType
{

    private $url;
    private $apiKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->url = $params->get('url');
        $this->apiKey = $params->get('apiKey');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('surname',null,[
                'required'=>true,
                'constraints'=>[
                    new Length([
                        'min'=>3,
                        'minMessage'=>'Минимальное число символов - {{ limit }}',
                        'max'=>255,
                    ]),
                    new Regex([
                        'match' => true,
                        'pattern' => '/^[а-яё -]+$/ui',
                        'message' => 'Допустимо написание русскими буквами,пробелами и дефисами',
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
                        'min'=>3,
                        'minMessage'=>'Минимальное число символов - {{ limit }}',
                        'max'=>255,
                    ]),
                    new Regex([
                        'match'=>true,
                        'pattern'=>'/^[а-яё -]+$/ui',
                        'message'=>'Допустимо написание русскими буквами,пробелами и дефисами'
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
                        'maxMessage'=>'Максимальное число символов - {{ limit }}',
                        'max'=>255,
                    ]),
                    new Regex([
                        'match'=>true,
                        'pattern'=>'/^[а-яё -]+$/ui',
                        'message'=>'Допустимо написание русскими буквами,пробелами и дефисами'
                    ]),
                ],
                'label'=>'Отчество',
                'attr'=>[
                    'class'=>'validate',
                ],
            ])

            ->add('phone',null,[
                'required'=>true,
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

            ->add('email',EmailType::class,[
                'required'=>true,
                'constraints' => [
                    new Email([
                        'message' => 'Email введен не корректно!',
                    ]),
                    new Length([
                        'max' => 255,
                    ]),
                ],
                'label' => 'Электронная почта',
                'attr' => [
                    'class' => 'validate',
                ],
            ])

            ->add('address',null,[
                'required'=>true,
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

            ->add('delivery_type', ChoiceType::class, [
                'choice_loader' => new CallbackChoiceLoader(function() {
                    return ReferenceApi::getDeliveryTypes($this->url, $this->apiKey);
                }),
                'label' => 'Способ доставки',
            ])

            ->add('payment_type', ChoiceType::class, [
                'choice_loader' => new CallbackChoiceLoader(function() {
                    return ReferenceApi::getPaymentsTypes($this->url, $this->apiKey);
                }),
                'label' => 'Способ оплаты',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}