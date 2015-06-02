<?php
/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

interface BackendRegistryInterface
{
    /**
     * Sets a payment backend identified by a alias.
     *
     * @param string           $alias   The payment backend alias.
     * @param BackendInterface $backend The payment backend.
     *
     * @return BackendRegistryInterface
     */
    public function setBackend($alias, BackendInterface $backend);

    /**
     * Returns a payment backend by alias.
     *
     * @param string $alias The alias of the payment backend.
     *
     * @return BackendInterface The payment backend
     *
     * @throws Exception\UnexpectedTypeException  If the passed alias is not a string.
     * @throws Exception\InvalidArgumentException If the payment backend can not be retrieved.
     */
    public function getBackend($alias);

    /**
     * Returns whether the given payment backend is supported.
     *
     * @param string $alias The alias of the payment backend.
     *
     * @return bool Whether the payment backend is supported.
     */
    public function hasBackend($alias);
}