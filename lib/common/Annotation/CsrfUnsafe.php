<?php

namespace CsrDelft\common\Annotation;

/**
 * Annotatie om CSRF uit te zetten voor deze route
 *
 * @see AccessControlEventListener hier wordt deze annotatie gecontroleerd
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class CsrfUnsafe
{
}
