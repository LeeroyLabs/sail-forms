<?php
namespace Leeroy\Forms;

use Leeroy\Forms\Controllers\FormLayout;
use Leeroy\Forms\Controllers\Form;
use SailCMS\Collection;
use SailCMS\Contracts\AppModule;
use SailCMS\Errors\GraphqlException;
use SailCMS\GraphQL;
use SailCMS\Types\ModuleInformation;

class Module implements AppModule
{
    public function info(): ModuleInformation
    {
        return new ModuleInformation('Forms', 'Form tool for SailCMS', 1.0, '1.0.0');
    }

    /**
     * @throws GraphqlException
     */
    public function graphql(): void
    {
        // Schema
        GraphQL::addQuerySchema(__DIR__ . '/Graphql/queries.graphql');
        GraphQL::addMutationSchema(__DIR__ . '/Graphql/mutations.graphql');
        GraphQL::addTypeSchema(__DIR__ . '/Graphql/types.graphql');

        // Queries
        GraphQL::addQueryResolver('formType', Form::class, 'formType');
        GraphQL::addQueryResolver('formTypes', Form::class, 'formTypes');
        GraphQL::addQueryResolver('formLayout', FormLayout::class, 'formLayout');
        GraphQL::addQueryResolver('formLayouts', FormLayout::class, 'formLayouts');

        // Mutations
        GraphQL::addMutationResolver('createFormType', Form::class, 'createFormType');
        GraphQL::addMutationResolver('updateFormType', Form::class, 'updateFormType');
        GraphQL::addMutationResolver('deleteFormType', Form::class, 'deleteFormType');
        GraphQL::addMutationResolver('createFormLayout', FormLayout::class, 'createFormLayout');
        GraphQL::addMutationResolver('updateFormLayoutSchema', FormLayout::class, 'updateFormLayoutSchema');
        GraphQL::addMutationResolver('updateFormLayoutSchemaKey', FormLayout::class, 'updateFormLayoutSchemaKey');
        GraphQL::addMutationResolver('deleteFormLayout', FormLayout::class, 'deleteFormLayout');
    }

    public function cli(): Collection
    {
        return Collection::init();
    }

    public function middleware(): void
    {
    }

    public function events(): void
    {
        // register for events
    }
}