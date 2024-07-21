<?php
declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\DTO\TransformStateInput;
use ElityDEV\StateMachine\Service\PersistenceAwareTransitionApplier;

readonly class TransformStateProcessor implements ProcessorInterface
{
    public function __construct(
        private PersistenceAwareTransitionApplier $transitionApplier,
    ) {
    }

    /**
     * @param TransformStateInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->transitionApplier->applyTransition(
            object: $context['previous_data'],
            transition: $data->getTransition(),
            stateMachineClass: $data->getStateMachineClass(),
        );

        return $context['previous_data'];
    }
}
