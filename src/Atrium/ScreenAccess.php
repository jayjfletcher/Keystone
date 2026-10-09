<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use RefactorCircus\Atrium\Support\ScreenAccess as AtriumScreenAccess;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

/**
 * Keystone's side of Atrium's ScreenAccess: whether the signed-in user may
 * perform an ability, asked the way the JSON API and MCP tools ask it through
 * the policies in `keystone.policies`. Views hide controls with it (as
 * `@keystoneCan`), so a control is shown exactly when its action is allowed.
 * With `keystone.authorization` off, everything is allowed.
 */
final class ScreenAccess
{
    /**
     * @param  Model|class-string<Model>  $subject
     */
    public static function allows(string $ability, Model|string $subject, ?Request $request = null): bool
    {
        return AtriumScreenAccess::allows('keystone', $ability, $subject, $request);
    }

    /**
     * Imports need `create` on products and exports `viewAny`, as the API's
     * start-import and start-export calls ask; the page opens for either.
     */
    public static function transfers(?Request $request = null): bool
    {
        return self::allows('create', ProductModel::class, $request) || self::allows('viewAny', ProductModel::class, $request);
    }
}
