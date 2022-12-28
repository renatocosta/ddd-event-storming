<?php

namespace Common\Specification;

/**
 * Interface it is for create a specification that is able to tell if a candidate object matches some criteria.
 * The specification has a method isSatisfiedBy (anObject) : Boolean that returns true if all criteria are met by anObject.
 */
interface Specification
{

    /**
     * @param mixed $object
     * @return bool
     */
    public function isSatisfiedBy($object): bool;

    /**
     * @param Specification $specification
     * @return Specification
     */
    public function andSpecification(Specification $specification): Specification;

    /**
     * @param Specification $specification
     * @return Specification
     */
    public function orSpecification(Specification $specification): Specification;

    /**
     * @return Specification
     */
    public function not(): Specification;

}