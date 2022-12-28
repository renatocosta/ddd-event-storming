<?php

namespace Common\Application\Orchestration;

use Generator;

abstract class UseCasesOrchestrator
{

    protected array $statements = [];

    /**
     *
     * @param  mixed  $initialInput
     */
    abstract protected function loadUseCases(mixed $initialInput): Generator;

    /**
     *
     * @param  object  $useCase
     */
    abstract protected function returnNextStatementFrom($useCase): array;

    /**
     *
     * @param  mixed  $initialInput
     */
    public function execute($initialInput): void
    {

        $useCases = $this->loadUseCases($initialInput);

        foreach ($useCases as $useCase => $input) {

            if (is_null($input)) continue;

            $useCase->execute($input);
            $this->statements = $this->returnNextStatementFrom($useCase);
        }
    }
}
