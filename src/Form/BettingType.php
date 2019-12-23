<?php

namespace App\Form;

use App\Entity\Bet;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * RaceBettingType constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
            'label' => false
        ]);
        $builder->add('submit', SubmitType::class, [
            'attr' => ['class' => 'btn btn-new']
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form form-inline',
                'padding' => '10px'
            ],
            'data_class' => Bet::class,
        ]);
    }
}
