<?php

declare(strict_types=1);

namespace App\Command;

use App\A;
use App\TheModel;
use Doctrine\Common\Collections\ArrayCollection;
use JetBrains\PhpStorm\NoReturn;
use JsonMapper\Handler\FactoryRegistry;
use JsonMapper\Handler\PropertyMapper;
use JsonMapper\JsonMapperBuilder;
use JsonMapper\JsonMapperInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:test')]

class TestCommand extends Command
{
    #[NoReturn]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $a = $this->acquireJsonMapper()->mapToClassFromString($this->getTargetJson(), A::class);

        dd($a);
    }

    private function acquireJsonMapper(): JsonMapperInterface
    {
        $classFactory = new FactoryRegistry();

        $builder = new JsonMapperBuilder();
        $builder->withTypedPropertiesMiddleware();
        $builder->withPropertyMapper(new PropertyMapper($classFactory));
        $mapper = $builder->build();

        $classFactory->addFactory(
            A::class,
            fn(\stdClass $data) => new ArrayCollection($mapper->mapToClassArray($data->results, TheModel::class))
        );

        return $mapper;
    }

    private function getTargetJson(): string
    {
        return <<<JSON
        {
            "results": [{
                "id": 1234,
                "name": "Naam A"
            }, {
                "id": 4567,
                "name": "Naam B"
            }]
        }
        JSON;
    }

}
