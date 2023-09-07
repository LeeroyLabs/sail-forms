<?php

namespace Leeroy\Forms\Controllers;

use JsonException;
use SailCMS\Collection;
use SailCMS\Contracts\AppController;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\EntryException;
use SailCMS\Errors\FieldException;
use SailCMS\Errors\PermissionException;
use SailCMS\GraphQL\Context;
use Leeroy\Forms\Models\FormLayout as FormLayoutModel;
use SailCMS\Models\EntryLayout;
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
     * @return array|null
     * @throws ACLException
     * @throws DatabaseException
     * @throws EntryException
     * @throws PermissionException
     * @throws FieldException
     * @throws FieldException
     * @throws EntryException
     *
     */
    public function createFormLayout(mixed $obj, Collection $args, Context $context): ?array
    {
        $titles = $args->get('titles');
        $graphqlSchema = $args->get('schema');
        $slug = $args->get('slug');

        $titles = new LocaleField($titles->unwrap());

        $schema = FormLayoutModel::processSchemaFromGraphQL($graphqlSchema);
        $generatedSchema = FormLayoutModel::generateLayoutSchema($schema);

        $formLayoutModel = new FormLayoutModel();
        return $formLayoutModel->create($titles, $generatedSchema, $slug)->simplify();
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
    public function updateFormLayoutSchema(mixed $obj, Collection $args, Context $context): bool
    {
        $id = $args->get('id');
        $titles = $args->get('titles');
        $schemaUpdate = $args->get('schema_update');

        $formLayoutModel = new FormLayoutModel();
        $formLayout = $formLayoutModel->one(['_id' => $id]);

        if (!$formLayout) {
            throw new EntryException(sprintf(EntryLayout::DOES_NOT_EXISTS, $id));
        }

        FormLayoutModel::updateSchemaFromGraphQL($schemaUpdate, $formLayout);

        return $formLayoutModel->updateById($id, $titles, $formLayout->schema);
    }

    /**
     *
     * Update a key in an form layout schema
     *
     * @param  mixed       $obj
     * @param  Collection  $args
     * @param  Context     $context
     * @return bool
     * @throws ACLException
     * @throws DatabaseException
     * @throws EntryException
     * @throws PermissionException
     * @throws JsonException
     *
     */
    public function updateFormLayoutSchemaKey(mixed $obj, Collection $args, Context $context): bool
    {
        $id = $args->get('id');
        $key = $args->get('key');
        $newKey = $args->get('newKey');

        $formLayoutModel = new FormLayoutModel();
        $formLayout = $formLayoutModel->one(['_id' => $id]);

        if (!$formLayout) {
            throw new EntryException(sprintf(EntryLayout::DOES_NOT_EXISTS, $id));
        }

        return $formLayout->updateSchemaKey($key, $newKey);
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