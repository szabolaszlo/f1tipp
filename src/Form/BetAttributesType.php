<?php


namespace App\Form;


use App\Entity\BetAttribute;
use App\Rule\RuleRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BetAttributesType extends AbstractType
{
    /**
     * @var RuleRegistry
     */
    protected $ruleRegistry;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * RaceBettingType constructor.
     * @param RuleRegistry $ruleRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(RuleRegistry $ruleRegistry, EntityManagerInterface $em)
    {
        $this->ruleRegistry = $ruleRegistry;
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('key', HiddenType::class);
        $builder->add('value', ChoiceType::class, [
            'choices' => []
        ]);

        // Listener for dynamic choose list for drivers, or question
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $betAttribute = $event->getData();
            if ($betAttribute instanceof BetAttribute) {
                $form->remove('value');
                $form->add('value', ChoiceType::class, [
                    'choices' => $this->getChoices(
                        $betAttribute->getBet()->getEvent()->getType(),
                        $form->get('key')->getData()),
                    'label' => 'betting_' . $form->get('key')->getData(),
                    'attr' => ['class' => 'form-control']
                ]);
            }
        });
    }

    protected function getChoices($type, $key)
    {
        $rule = $this->ruleRegistry->getRuleByType($type);

        $ruleAttribute = $rule->getAttributeById($key);

        //TODO Make some provider or something instead of this wtf
        if ($ruleAttribute->getType() == 'driver') {
            return $this->em->getRepository('App:Driver')->getDriverChoices();
        }

        return [
            'Nem' => 'Nem',
            'Igen' => 'Igen'
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BetAttribute::class,
        ]);
    }
}