<?php

declare(strict_types=1);

use JayI\Cortex\Domains\McpServer\Actions\CreateMcpInstructionVersionAction;
use JayI\Cortex\Domains\McpServer\Services\McpServerRegistry;
use JayI\Cortex\Domains\Tool\Actions\CreateToolDescriptionVersionAction;
use JayI\Cortex\Domains\Tool\Services\ToolRegistry;
use JayI\Foundation\Cortex\CortexIntegration;
use JayI\Foundation\Packages\PackageRegistry;
use JayI\Keystone\Domains\Attribute\Mcp\Tools\ListAttributesTool;
use JayI\Keystone\Mcp\KeystoneServer;
use Laravel\Ai\Contracts\Tool as AgentTool;
use Laravel\Ai\Tools\Request;
use Laravel\Mcp\Server\Transport\FakeTransporter;

it('registers the MCP server with Cortex', function (): void {
    $servers = app(McpServerRegistry::class);

    expect($servers->has('keystone'))->toBeTrue()
        ->and($servers->get('keystone'))->toBe(KeystoneServer::class)
        ->and($servers->defaultInstructions('keystone'))->toStartWith('Manage the Keystone product catalog');
});

it('offers every Keystone tool to Cortex agents under its own name', function (): void {
    $tools = app(ToolRegistry::class);

    $names = array_map(fn (string $class): string => app($class)->name(), KeystoneServer::TOOLS);

    expect(KeystoneServer::TOOLS)->toHaveCount(78)
        ->and(array_diff($names, $tools->names()))->toBe([])
        ->and($tools->get('list-attributes-tool'))->toBeInstanceOf(AgentTool::class)
        ->and($tools->tagsFor('list-attributes-tool'))->toContain('keystone');
});

it('offers only the tools listed in config', function (): void {
    config()->set('keystone.cortex.tools', ['list-attributes-tool', 'show-attribute-tool']);
    app()->forgetInstance(ToolRegistry::class);

    $tools = app(ToolRegistry::class);

    expect($tools->has('list-attributes-tool'))->toBeTrue()
        ->and($tools->has('show-attribute-tool'))->toBeTrue()
        ->and($tools->has('create-attribute-tool'))->toBeFalse();
});

it('serves the instructions published in Cortex', function (): void {
    app(CreateMcpInstructionVersionAction::class)->execute('keystone', ['content' => 'Never delete attributes.', 'publish' => true]);

    expect((new KeystoneServer(new FakeTransporter))->createContext()->instructions)->toBe('Never delete attributes.');
});

it('serves tool descriptions published in Cortex, to MCP clients and agents alike', function (): void {
    app(CreateToolDescriptionVersionAction::class)->execute('list-attributes-tool', ['content' => 'List the attributes of our storefront catalog.', 'publish' => true]);

    expect(app(ListAttributesTool::class)->description())->toBe('List the attributes of our storefront catalog.')
        ->and(app(ToolRegistry::class)->get('list-attributes-tool')->description())->toBe('List the attributes of our storefront catalog.');
});

it('lets an agent call an Keystone tool with its arguments', function (): void {
    // The tool takes its own request class; without the arguments it could
    // not look the attribute up at all.
    $result = (string) app(ToolRegistry::class)->get('show-attribute-tool')->handle(new Request(['attribute' => 'missing']));

    expect($result)->toContain('Not found.');
});

it('stays out of Cortex when turned off', function (): void {
    $integration = CortexIntegration::for(app(PackageRegistry::class)->get('keystone'));

    expect($integration->active())->toBeTrue();

    config()->set('keystone.cortex.enabled', false);

    expect($integration->active())->toBeFalse()
        ->and($integration->instructions())->toBeNull()
        ->and($integration->description('list-attributes-tool'))->toBeNull();
});
