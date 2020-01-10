<?php

namespace App\Form;

use App\Builder\ResultBuilder;
use App\Entity\Result;
use App\Form\Constraint\UniqueBetAttributes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ResultType
 * @package App\Form
 */
class ResultType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ResultBuilder
     */
    protected $resultBuilder;

    /**
     * ResultType constructor.
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param ResultBuilder $resultBuilder
     */
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, ResultBuilder $resultBuilder)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->resultBuilder = $resultBuilder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('event', HiddenType::class);
        $builder->add('isCalculated', HiddenType::class, [
            'inherit_data' => false,
            'data' => 0
        ]);
        $builder->add('attributes', CollectionType::class, [
            'entry_type' => ResultAttributesType::class,
            'allow_add' => false,
            'entry_options' => ['label' => false],
            'label' => false,
            'constraints' => new UniqueBetAttributes()
        ]);

        $builder->get('event')->addModelTransformer(new CallbackTransformer(
            function ($event) {
                // transform the Event Object to integer
                return $event ? $event->getId() : null;
            },
            function ($eventId) {
                // transform the integer back to Event Object
                return $this->em->getRepository('App:Event')->find($eventId);
            }
        ));

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $result = $event->getData();

            if ($result instanceof Result && $result->getEvent()) {
                $form->add('submit', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn-new event-submit-' . $result->getEvent()->getId(),
                    ],
                    'label' => 'admin_result_upload_submit_' . $result->getEvent()->getType()
                ]);
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Result::class,
            'data' => $this->resultBuilder->build(),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // important part; unique key
            'csrf_token_id' => 'form_intention',
            'allow_extra_fields' => true
        ]);
    }
}