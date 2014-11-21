<?php namespace Anomaly\Streams\Platform\Ui\Form\Command;

/**
 * Class BuildFormActionsCommandHandler
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\Streams\Platform\Ui\Form\Command
 */
class BuildFormActionsCommandHandler
{

    /**
     * These are not attributes and won't
     * make it into the attribute string.
     *
     * @var array
     */
    protected $notAttributes = [
        'url',
        'slug',
        'title',
        'class',
    ];

    /**
     * Handle the command.
     *
     * @param BuildFormActionsCommand $command
     * @return array
     */
    public function handle(BuildFormActionsCommand $command)
    {
        $actions = [];

        $form = $command->getForm();

        $entry      = $form->getEntry();
        $presets    = $form->getPresets();
        $expander   = $form->getExpander();
        $evaluator  = $form->getEvaluator();
        $normalizer = $form->getNormalizer();

        /**
         * Loop through and process actions configurations.
         */
        foreach ($form->getActions() as $slug => $action) {

            // Expand, automate and evaluate.
            $action = $expander->expand($slug, $action);
            $action = $presets->setActionPresets($action);
            $action = $evaluator->evaluate($action, compact('form'), $entry);

            // Skip if disabled.
            if (array_get($action, 'enabled') === false) {

                continue;
            }

            // Build out our required data.
            $title = array_get($action, 'title');
            $class = array_get($action, 'class', 'btn btn-sm btn-success');

            $attributes = $this->getAttributes($action, $form);

            // Normalize the result.
            $action = $normalizer->normalize(compact('title', 'class', 'attributes'));

            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * Get the attributes. This is the entire array
     * less the keys marked as "not attributes".
     *
     * @param array $action
     * @return array
     */
    protected function getAttributes(array $action)
    {
        // URL is actually the href
        $action['href'] = array_get($action, 'url', '#');

        return array_diff_key($action, array_flip($this->notAttributes));
    }
}
 