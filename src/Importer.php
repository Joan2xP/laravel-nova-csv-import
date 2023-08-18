<?php

namespace SimonHamp\LaravelNovaCsvImport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Nova\Resource;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;
use SimonHamp\LaravelNovaCsvImport\Concerns\HasModifiers;

use Illuminate\Support\Facades\DB;


class Importer implements ToModel, WithValidation, WithHeadingRow, WithMapping, WithBatchInserts, WithChunkReading, SkipsOnFailure, SkipsOnError, SkipsEmptyRows
{
    use Importable, SkipsFailures, SkipsErrors, HasModifiers;

    protected Resource $resource;

    protected $resourceFields = [];

    protected $attribute_map = [];

    protected $rules;

    protected $model_class;

    protected $meta_values = [];

    protected $custom_values = [];

    public function __construct()
    {
        $this->bootHasModifiers();
    }

    public function map($row): array
    {
        if (empty($this->attribute_map)) {
            return $row;
        }

        $data = [];

        foreach ($this->attribute_map as $attribute => $column) {
            if (!$column) {
                continue;
            }

            $data[$attribute] = $this->modifyValue(
                $this->getFieldValue($row, $column, $attribute),
                $this->getModifiers($attribute)
            );
        }

        return $data;
    }

    public function model(array $row): Model
    {
        $model = $this->resource::newModel();


        if ($this->resourceFields == []) {
            $this->resourceFields = json_decode(json_encode($this->resource), true)["fields"];
        }

        DB::beginTransaction();
        try {
            $model->fill($row);
            foreach ($row as $key => $value) {
                foreach ($this->resourceFields as $field) {
                    if (isset($field["belongsToRelationship"]) && $field["belongsToRelationship"] == $key) {
                        $model_name = sprintf('App\Models\%s', $field["indexName"]);
                        $tipus =   $model_name::firstOrCreate(["nom" => $value]);

                        $model->$key()->associate($tipus);
                    } else if (isset($field["component"]) && $field["component"] == "BelongsToManyField" && $field["attribute"] == $key) {
                        $model->save();

                        $tags = explode(",", $value);

                        foreach ($tags as $tag) {
                            $model_name = sprintf('App\Models\%s', Str::singular(ucfirst($field["attribute"])));
                            $tag_model = $model_name::firstOrCreate(["nom" => trim($tag)]);

                            $model->$key()->attach($tag_model);
                        }
                    }
                }

                $nomParts = explode("_", $key);
                if ($nomParts[0] == "translations") {

                    $model->setTranslation($nomParts[1], $nomParts[2], $value);
                    unset($row[$key]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }






        return $model;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getAttributeMap(): array
    {
        return $this->attribute_map;
    }

    public function setAttributeMap(array $map): self
    {
        $this->attribute_map = $map;

        return $this;
    }

    public function getMeta($key = null)
    {
        if ($key && !empty($this->meta_values[$key])) {
            return $this->meta_values[$key];
        }

        return $this->meta_values;
    }

    public function setMeta(array $meta): self
    {
        $this->meta_values = $meta;

        return $this;
    }

    public function getCustomValues($key = null)
    {
        if ($key) {
            return $this->custom_values[$key] ?? '';
        }

        return $this->custom_values;
    }

    public function setCustomValues(array $map): self
    {
        $this->custom_values = $map;

        return $this;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function getModelClass(): string
    {
        return $this->model_class;
    }

    public function setModelClass(string $model_class): self
    {
        $this->model_class = $model_class;

        return $this;
    }

    public function setResource(Resource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    protected function getFieldValue(array $row, string $mapping, string $attribute)
    {
        if (array_key_exists($mapping, $row)) {
            return $row[$mapping];
        } elseif (Str::startsWith($mapping, 'meta')) {
            return $this->getMeta(Str::remove('@meta.', "@{$mapping}"));
        } elseif ($mapping === 'custom') {
            return $this->getCustomValues($attribute);
        }
    }
}
