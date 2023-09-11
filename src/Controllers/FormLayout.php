<?php

namespace Leeroy\Forms\Controllers;

use JsonException;
use SailCMS\Collection;
use SailCMS\Contracts\AppController;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\EntryException;
use SailCMS\Errors\PermissionException;
use SailCMS\GraphQL\Context;
use Leeroy\Forms\Models\FormLayout as FormLayoutModel;
use SailCMS\Models\EntryLayout;
use SailCMS\Types\EntryLayoutTab;
use SailCMS\Types\LocaleField;

class FormLayout extends AppController
{
    /**
     *
     * Get a form layout by id
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return array
     *
     */
    public function formLayout(mixed $obj, Collection $args, Context $context): array
    {
        $formLayoutId = $args->get('id');

        $formLayoutModel = new FormLayoutModel();
        $formLayout = $formLayoutModel->one([
            '_id' => $formLayoutId
        ]);

        return $formLayout?->simplify();
    }

    /**
     *
     * Get all form layouts
     *
     * @param  mixed       $obj
     * @param  Collection  $args
     * @param  Context     $context
     * @return array|null
     * @throws ACLException
     * @throws DatabaseException
     * @throws PermissionException
     *
     */
    public function formLayouts(mixed $obj, Collection $args, Context $context): ?array
    {
        $formLayouts = Collection::init();
        $result = (new FormLayoutModel())->getAll() ?? [];

        (new Collection($result))->each(function ($key, $formLayout) use ($formLayouts) {
            $formLayouts->push($formLayout->simplify());
        });

        return $formLayouts->unwrap();
    }

    /**
     *
     * Create a form layout
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return EntryLayout
     * @throws ACLException
     * @throws DatabaseException
     * @throws EntryException
     * @throws PermissionException
     * @throws EntryException
     *
     */
    public function createFormLayout(mixed $obj, Collection $args, Context $context): EntryLayout
    {
        $titles = $args->get('title');
        $graphqlSchema = $args->get('schema');
        $slug = $args->get('slug');

        $schema = Collection::init();
        foreach ($graphqlSchema as $tab) {
            $fields = $tab->fields->unwrap() ?? [];

            $schema->push(new EntryLayoutTab($tab->label, $fields));
        }

        return (new FormLayoutModel())->create($titles, $schema, $slug);
    }

    /**
     *
     * Update a form layout
     *
     * @param  mixed       $obj
     * @param  Collection  $args
     * @param  Context     $context
     * @return bool
     * @throws ACLException
     * @throws DatabaseException
     * @throws EntryException
     * @throws PermissionException
     *
     */
    public function updateFormLayout(mixed $obj, Collection $args, Context $context): bool
    {
        $graphqlSchema = $args->get('schema');
        $id = $args->get('id');
        $title = $args->get('title');
        $schema = null;

        if (!$graphqlSchema && !$title && !$args->get('slug')) {
            throw new EntryException(EntryLayout::NOTHING_TO_UPDATE);
        }

        // Process schema from graphql
        if ($graphqlSchema) {
            $schema = Collection::init();
            foreach ($graphqlSchema as $tab) {
                $fields = $tab->fields->unwrap() ?? [];

                $schema->push(new EntryLayoutTab($tab->label, $fields));
            }
        }

        return (new FormLayoutModel())->updateById($id, $title, $schema, $args->get('slug'));
    }

    /**
     *
     * Delete a form layout
     *
     * @param  mixed       $obj
     * @param  Collection  $args
     * @param  Context     $context
     * @return bool
     * @throws ACLException
     * @throws DatabaseException
     * @throws EntryException
     * @throws PermissionException
     *
     */
    public function deleteFormLayout(mixed $obj, Collection $args, Context $context): bool
    {
        $ids = $args->get('ids')->unwrap();
        $soft = $args->get('soft', true);

        return (new FormLayoutModel())->deleteManyByIds($ids, $soft);
    }
}