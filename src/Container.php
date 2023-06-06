<?php
namespace Leeroy\Forms;

use Leeroy\Forms\Controllers\FormEntry;
use Leeroy\Forms\Controllers\FormLayout;
use Leeroy\Forms\Controllers\Form;
use SailCMS\Collection;
use SailCMS\Contracts\AppContainer;
use SailCMS\Errors\GraphqlException;
use SailCMS\GraphQL;
use SailCMS\Types\ContainerInformation;

class Container extends AppContainer
{
    public function info(): ContainerInformation
    {
        return new ContainerInformation('Forms', 'Form tool for SailCMS', 1.0, '1.0.0');
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
        GraphQL::addQueryResolver('form', Form::class, 'form');
        GraphQL::addQueryResolver('forms', Form::class, 'forms');
        GraphQL::addQueryResolver('formEntry', FormEntry::class, 'formEntry');
        GraphQL::addQueryResolver('formEntries', FormEntry::class, 'formEntries');
        GraphQL::addQueryResolver('formLayout', FormLayout::class, 'formLayout');
        GraphQL::addQueryResolver('formLayouts', FormLayout::class, 'formLayouts');

        // Mutations
        GraphQL::addMutationResolver('createForm', Form::class, 'createForm');
        GraphQL::addMutationResolver('updateForm', Form::class, 'updateForm');
        GraphQL::addMutationResolver('deleteForm', Form::class, 'deleteForm');
        GraphQL::addMutationResolver('createFormEntry', FormEntry::class, 'createFormEntry');
        GraphQL::addMutationResolver('updateFormEntry', FormEntry::class, 'updateFormEntry');
        GraphQL::addMutationResolver('deleteFormEntry', FormEntry::class, 'deleteFormEntry');
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

    public function routes(): void
    {
    }

    public function configureSearch(): void
    {
    }

    public function permissions(): Collection
    {
        return Collection::init();
    }

    public function fields(): Collection
    {
        return Collection::init();
    }
}