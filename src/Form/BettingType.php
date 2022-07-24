<?php

namespace App\Form;

use App\Entity\Bet;
use App\Entity\Event;
use App\Entity\User;
use App\Form\Constraint\UniqueBetAttributes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BettingType
 * @package App\Form
 */
class BettingType extends AbstractType
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
     * BettingType constructor.
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', HiddenType::class);
        $builder->add('event', HiddenType::class);
        $builder->add('attributes', CollectionType::class, [
            'entry_type' => BetAttributesType::class,
            'allow_add' => true,
            'entry_options' => ['label' => false],
            'label' => false,
            'constraints'=> new UniqueBetAttributes()
        ]);

        $builder->get('user')->addModelTransformer(new CallbackTransformer(
            function (User $user) {
                // transform the User Object to integer
                return $user->getId();
            },
            function ($userId) {
                // transform the integer back to User Object
                return $this->em->getRepository('App:User')->find($userId);
            }
        ));

        $builder->get('event')->addModelTransformer(new CallbackTransformer(
            function (Event $event) {
                // transform the Event Object to integer
                return $event->getId();
            },
            function ($eventId) {
                // transform the integer back to Event Object
                return $this->em->getRepository('App:Event')->find($eventId);
            }
        ));

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $bet = $event->getData();

            if ($bet instanceof Bet && !$bet->getId()) {
                $now = new \DateTime();
                if ($now < $bet->getEvent()->getDateTime()) {
                    $form->add('submit', ButtonType::class, [
                        'attr' => [
                            'id' => 'betting-form-submit',
                            'class' => 'btn-new event-submit-' . $bet->getEvent()->getId(),
                        ],
                        'label' => 'betting_submit_' . $bet->getEvent()->getType()
                    ]);
                } else {
                    $form->remove('attributes');
                    $form->addError(new FormError($this->translator->trans('betting_time_out')));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form form-inline',
                'padding' => '10px',
                'id' => 'betting-form'
            ],
            'data_class' => Bet::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // important part; unique key
            'csrf_token_id' => 'form_intention',
            'allow_extra_fields' => true
        ]);
    }
}
