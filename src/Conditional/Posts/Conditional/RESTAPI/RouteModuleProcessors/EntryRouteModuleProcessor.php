<?php
namespace PoP\Users\Conditional\Posts\Conditional\RESTAPI\RouteModuleProcessors;

use PoP\ModuleRouting\AbstractEntryRouteModuleProcessor;
use PoP\ComponentModel\Engine_Vars;
use PoP\Hooks\Facades\HooksAPIFacade;
use PoP\API\Facades\FieldQueryConvertorFacade;
use PoP\Users\Routing\RouteNatures;

class EntryRouteModuleProcessor extends AbstractEntryRouteModuleProcessor
{
    private static $restFieldsQuery;
    private static $restFields;
    public static function getRESTFields(): array
    {
        if (is_null(self::$restFields)) {
            self::$restFields = self::getRESTFieldsQuery();
            if (is_string(self::$restFields)) {
                self::$restFields = FieldQueryConvertorFacade::getInstance()->convertAPIQuery(self::$restFields);
            }
        }
        return self::$restFields;
    }
    public static function getRESTFieldsQuery(): string
    {
        if (is_null(self::$restFieldsQuery)) {
            // Same as for posts, but removing the user data
            self::$restFieldsQuery = (string) HooksAPIFacade::getInstance()->applyFilters(
                'Users:Posts:RESTFields',
                str_replace(
                    ','.\PoP\Users\Conditional\Posts\Hooks\HookSet::AUTHOR_RESTFIELDS,
                    '',
                    \PoP\Posts\Conditional\RESTAPI\RouteModuleProcessors\EntryRouteModuleProcessor::getRESTFieldsQuery()
                )
            );
        }
        return self::$restFieldsQuery;
    }

    public function getModulesVarsPropertiesByNatureAndRoute()
    {
        $ret = array();
        $vars = Engine_Vars::getVars();
        // Author's posts
        $routemodules = array(
            POP_POSTS_ROUTE_POSTS => [\PoP_Users_Posts_Module_Processor_FieldDataloads::class, \PoP_Users_Posts_Module_Processor_FieldDataloads::MODULE_DATALOAD_DATAQUERY_AUTHORPOSTLIST_FIELDS, ['fields' => isset($vars['query']) ? $vars['query'] : self::getRESTFields()]],
        );
        foreach ($routemodules as $route => $module) {
            $ret[RouteNatures::USER][$route][] = [
                'module' => $module,
                'conditions' => [
                    'scheme' => POP_SCHEME_API,
                    'datastructure' => GD_DATALOAD_DATASTRUCTURE_REST,
                ],
            ];
        }
        return $ret;
    }
}