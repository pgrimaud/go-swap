<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Type;
use App\Entity\TypeEffectiveness as TypeEffectivenessEntity;
use App\Repository\TypeEffectivenessRepository;

class TypeEffectiveness
{
    public function __construct(private TypeEffectivenessRepository $typeEffectivenessRepository)
    {
    }

    /**
     * @return array<Type>
     */
    public function getStrongAgainst(Type $type): array
    {
        $results = [];

        /** @var TypeEffectivenessEntity[] $typeEffectiveness */
        $typeEffectiveness = $this->typeEffectivenessRepository->getStrongAgainst($type);
        foreach($typeEffectiveness as $effectiveness) {
            /** @var Type $result */
            $result = $effectiveness->getTargetType();
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @return array<Type>
     */
    public function getVulnerableTo(Type $type): array
    {
        $results = [];

        /** @var TypeEffectivenessEntity[] $typeEffectiveness */
        $typeEffectiveness = $this->typeEffectivenessRepository->getVulnerableTo($type);
        foreach($typeEffectiveness as $effectiveness) {
            /** @var Type $result */
            $result = $effectiveness->getSourceType();
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @return array<Type>
     */
    public function getResistantTo(Type $type): array
    {
        $results = [];

        /** @var TypeEffectivenessEntity[] $typeEffectiveness */
        $typeEffectiveness = $this->typeEffectivenessRepository->getResistantTo($type);
        foreach($typeEffectiveness as $effectiveness) {
            /** @var Type $result */
            $result = $effectiveness->getSourceType();
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @return array<Type>
     */
    public function getNotEffectiveAgainst(Type $type): array
    {
        $results = [];

        /** @var TypeEffectivenessEntity[] $typeEffectiveness */
        $typeEffectiveness = $this->typeEffectivenessRepository->getNotEffectiveAgainst($type);
        foreach($typeEffectiveness as $effectiveness) {
            /** @var Type $result */
            $result = $effectiveness->getTargetType();
            $results[] = $result;
        }

        return $results;
    }
}