<?php

namespace App\Display;

use App\Entity\Driver;
use App\Entity\Event;
use App\Rule\RuleRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A tippelő Symfony formot JSON-ná alakítja, hogy a Vue saját maga renderelje
 * (TV-grafikás pilótaválasztó). A form marad a validáció és a CSRF forrása:
 * a mezőnevek (betting_form[...]) változatlanok, a kliens ezekkel küldi vissza.
 */
class BettingFormPresenter
{
    private RuleRegistry $ruleRegistry;
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;
    private TeamColorProvider $teamColors;

    public function __construct(
        RuleRegistry $ruleRegistry,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        TeamColorProvider $teamColors
    ) {
        $this->ruleRegistry = $ruleRegistry;
        $this->em = $em;
        $this->translator = $translator;
        $this->teamColors = $teamColors;
    }

    public function present(FormInterface $form, Event $event, ?string $successMessage = null): array
    {
        $view = $form->createView();
        $rule = $this->ruleRegistry->getRuleByType($event->getType());

        $hidden = [];
        foreach (['user', 'event', '_token'] as $name) {
            if (isset($view[$name])) {
                $hidden[] = $this->hidden($view[$name]);
            }
        }

        $fields = [];
        $driverCodes = [];
        $questionChoices = [];

        if (isset($view['attributes'])) {
            foreach ($view['attributes'] as $attributeView) {
                $hidden[] = $this->hidden($attributeView['key']);

                $key = (string) $attributeView['key']->vars['value'];
                $valueView = $attributeView['value'];
                $type = $rule->getAttributeById($key)->getType();
                $choices = $this->choiceValues($valueView);

                if ($type === 'driver') {
                    $driverCodes = array_merge($driverCodes, $choices);
                } else {
                    $questionChoices = array_map(fn ($value) => ['value' => $value, 'label' => $value], $choices);
                }

                $value = (string) $valueView->vars['value'];
                if ($type === 'driver' && $value !== '') {
                    $driverCodes[] = $value;
                }

                $fields[] = [
                    'name' => $valueView->vars['full_name'],
                    'key' => $key,
                    'label' => rtrim($this->translator->trans('betting_' . $key), ': '),
                    'type' => $type,
                    'value' => $value,
                    'disabled' => !empty($valueView->vars['attr']['disabled']),
                ];
            }
        }

        $errors = array_map(
            fn (FormError $error) => $error->getMessage(),
            iterator_to_array($form->getErrors(true))
        );

        return [
            'eventId' => $event->getId(),
            'type' => $event->getType(),
            'action' => $view->vars['action'],
            'submitLabel' => isset($view['submit'])
                ? $this->translator->trans($view['submit']->vars['label'])
                : null,
            'hidden' => $hidden,
            'fields' => $fields,
            'drivers' => $this->drivers(array_values(array_unique($driverCodes))),
            'questionChoices' => $questionChoices,
            'errors' => array_values(array_unique($errors)),
            'success' => $successMessage !== null ? $this->translator->trans($successMessage) : null,
        ];
    }

    private function hidden(FormView $view): array
    {
        return ['name' => $view->vars['full_name'], 'value' => (string) $view->vars['value']];
    }

    /**
     * @return string[]
     */
    private function choiceValues(FormView $view): array
    {
        $values = [];
        foreach ($view->vars['choices'] ?? [] as $choice) {
            if ($choice instanceof ChoiceView && $choice->value !== '') {
                $values[] = $choice->value;
            }
        }

        return $values;
    }

    /**
     * A form választási sorrendjét (VB-pontszám szerint) tartja; a választható listán
     * kívüli, már leadott tipp pilótája (pl. azóta inaktív) a lista végére kerül.
     */
    private function drivers(array $codes): array
    {
        if ($codes === []) {
            return [];
        }

        /** @var Driver[] $entities */
        $entities = $this->em->getRepository(Driver::class)->findBy(['short' => $codes]);
        $byCode = [];
        foreach ($entities as $driver) {
            $byCode[strtoupper($driver->getShort())] = $driver;
        }

        $drivers = [];
        foreach ($codes as $code) {
            $driver = $byCode[strtoupper($code)] ?? null;
            $name = DriverName::split($driver ? (string) $driver->getName() : $code);

            $drivers[] = [
                'code' => $code,
                'givenName' => $name['givenName'],
                'familyName' => $name['familyName'],
                'teamColor' => $this->teamColors->colorForDriver($code),
                'points' => $driver ? (int) $driver->getPoint() : null,
            ];
        }

        return $drivers;
    }
}
