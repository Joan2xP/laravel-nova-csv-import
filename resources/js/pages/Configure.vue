<template>
  <div>
    <Head>
      <title>Configurar import</title>
    </Head>

    <heading class="mb-6">Configurar import</heading>

    <card class="p-8 space-y-4 mb-8">
      <p>
        S'han descobert <b>{{ headings.length }}</b> columne(s) i
        <b>{{ total_rows }}</b>
        fila(es) en les teves dades.
      </p>

      <p>Aquí hi ha una mostra de les teves dades:</p>

      <hr />

      <div class="overflow-scroll">
        <table cellpadding="10">
          <thead class="border-b">
            <tr>
              <th v-for="heading in headings">
                <span class="font-bold">{{ heading }}</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows">
              <td v-for="col in row">
                <code>
                  {{ col }}
                  <i v-if="!col">res</i>
                </code>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </card>

    <card class="p-8 space-y-4 mb-8">
      <p>Escull un recurs al que importar aquestes dades.</p>

      <div class="inline-flex items-center">
        <b>Recurs:</b>
        <SelectControl
          @change="(value) => (resource = value)"
          :selected="resource"
          class="ml-4"
        >
          <option value="">- Escull un recurs -</option>
          <option v-for="(label, index) in resources" :value="index">
            {{ label }}
          </option>
        </SelectControl>
      </div>
    </card>

    <card class="p-8 space-y-4">
      <template v-if="resource">
        <p>
          Escull amb quines dades omplir els camps del recurs escollit. Les
          columnes de l'arxiu pujat s'han adjuntat automàticament amb els camps
          del recurs amb el mateix nom.
        </p>

        <p v-if="resource">
          Utilitza modificadors per modificar el valor <i>abans</i> que es desi
          a la teva font de dades. Els modificadors són combinatòris, es poden apilar junts,
          i s'executen en l'ordre definit.
        </p>

        <p><b>TIP</b>: Pots arrosegar els modifcadors per canviar el seu ordre.</p>

        <table cellpadding="10">
          <thead class="border-b">
            <tr>
              <th>Camp</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="field in fields[resource]" class="border-b">
              <td class="pr-2">
                <span class="font-bold">{{ field.name }}</span
                ><br />
                <small class="text-grey-300">{{ field.attribute }}</small>
              </td>
              <td class="space-y-2">
                <SelectControl
                  @change="(value) => (mappings[field.attribute] = value)"
                  :selected="mappings[field.attribute]"
                >
                  <option
                    value=""
                    v-if="field.rules.includes('required')"
                    disabled
                  >
                    - El camp és requerit -
                  </option>
                  <option value="" v-else>- Deixar el camp buit -</option>

                  <optgroup label="Columnes de l'arxiu">
                    <option v-for="heading in headings" :value="heading">
                      {{ heading }}
                    </option>
                  </optgroup>

                  <optgroup label="Meta data">
                    <option value="meta.file">
                      Nom del'arxiu (amb sufix): {{ file }}
                    </option>
                    <option value="meta.file_name">
                      Nom de l'arxiu (sense sufix): {{ file_name }}
                    </option>
                    <option value="meta.original_file">
                      Nom original del'arxiu (amb sufix):
                      {{ config.original_filename }}
                    </option>
                    <option value="meta.original_file_name">
                      Nom original del'arxiu (sense sufix):
                      {{ original_file_name }}
                    </option>
                  </optgroup>

                  <optgroup label="Personalitzat - ho mateix per tots">
                    <option value="custom">Un valor</option>
                  </optgroup>
                </SelectControl>

                <input
                  v-model="values[field.attribute]"
                  v-if="mappings[field.attribute] === 'custom'"
                  class="form-control form-input form-input-bordered"
                />

                <draggable
                  v-model="modifiers[field.attribute]"
                  handle=".handle"
                  item-key="modifier"
                >
                  <template #item="{ element, index }">
                    <div
                      class="flex mb-2 space-x-2 items-start border-rounded bg-gray-50 p-2 handle"
                    >
                      <span>{{ index + 1 }}</span>
                      <div class="flex flex-col flex-1 space-y-2">
                        <SelectControl
                          @change="(value) => (element.name = value)"
                          :selected="element.name"
                        >
                          <option value="">- No es modifica -</option>

                          <option v-for="mod in mods" :value="mod.name">
                            {{ mod.title }}
                          </option>
                        </SelectControl>

                        <label
                          v-for="(config, name) in mods[element.name].settings"
                          v-if="mods[element.name]?.settings"
                          class="flex items-center space-x-2"
                        >
                          <span>{{ config.title }}</span>

                          <SelectControl
                            v-if="config.type === 'select'"
                            @change="
                              (value) => (element.settings[name] = value)
                            "
                            :selected="element.settings[name]"
                          >
                            <option
                              v-for="(option, value) of config.options"
                              :value="value"
                              :selected="value === config.default"
                            >
                              {{ option }}
                            </option>
                          </SelectControl>

                          <input
                            type="text"
                            v-if="config.type === 'string'"
                            v-model="element.settings[name]"
                            class="form-control form-input form-input-bordered ml-4"
                            :placeholder="config.default"
                          />

                          <input
                            type="text"
                            v-if="config.type === 'boolean'"
                            v-model="element.settings[name]"
                            class="checkbox"
                            :checked="config.default"
                          />

                          <div class="help-text">{{ config.help }}</div>
                        </label>
                      </div>
                      <button @click="removeModifier(field.attribute, index)">
                        &times;
                      </button>
                    </div>
                  </template>
                </draggable>

                <button
                  @click="addModifier(field.attribute)"
                  v-if="mappings[field.attribute]"
                  class="cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring h-7 px-1 md:px-3"
                >
                  Afegir modificador
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </template>

      <div class="flex justify-center space-x-2">
        <LinkButton @click="goBack">
          &leftarrow; Puja un arxiu diferent
        </LinkButton>
        <DefaultButton :disabled="disabledSave" @click="saveConfig">
          {{ saving ? "Important..." : "Guarda &amp; Previsualitza &rightarrow;" }}
        </DefaultButton>
      </div>
    </card>
  </div>
</template>

<script>
import draggable from "vuedraggable";

export default {
  components: {
    draggable,
  },

  data() {
    return {
      resource: this.config?.resource || "",
      mappings: this.config?.mappings || {},
      values: this.config?.values || {},
      modifiers: this.config?.modifiers || {},
      saving: false,
    };
  },

  props: [
    "headings",
    "resources",
    "fields",
    "file",
    "file_name",
    "rows",
    "total_rows",
    "config",
    "mods",
  ],

  watch: {
    resource: {
      handler(newValue) {
        if (newValue === "") {
          return;
        }

        const fields = this.fields[newValue];

        // Restore original settings
        if (newValue === this.config?.resource) {
          this.mappings = this.config?.mappings || {};
          this.values = this.config?.values || {};

          return;
        }

        // Reset all of the mappings and any custom values
        for (let { name, attribute } of fields) {
          this.mappings[attribute] = "";
          this.values = {};
        }

        // For each field of the resource, try to find a matching heading and pre-assign
        for (let { name, attribute } of fields) {
          let heading = this.headings.indexOf(attribute);

          if (heading < 0) {
            continue;
          }

          // Because they're an exact match, we don't need to get the exact heading out
          this.mappings[attribute] = attribute;
        }
      },
      deep: true,
    },
  },

  methods: {
    removeModifier(attribute, index) {
      this.modifiers[attribute].splice(index, 1);
    },

    saveConfig() {
      if (!this.hasValidConfiguration()) {
        return;
      }

      this.saving = true;

      let data = {
        resource: this.resource,
        mappings: this.mappings,
        values: this.values,
        modifiers: this.modifiers,
        file: this.file,
      };

      Nova.request()
        .post(this.url("configure"), data)
        .then((response) => {
          if (response.status === 200) {
            Nova.success("Configuració guardada");
            Nova.visit("/csv-import/preview/" + this.file);
          }
        })
        .catch((e) => {
          this.saving = false;
          Nova.error("Hi ha hagut un problema guardant la teva configuració");
        });

      this.saving = false;
    },

    goBack() {
      Nova.visit("/csv-import/");
    },

    hasValidConfiguration() {
      const mappedColumns = [],
        mappings = this.mappings;

      Object.keys(mappings).forEach(function (key) {
        if (mappings[key] !== "") {
          mappedColumns.push(key);
        }
      });

      return this.resource !== "" && mappedColumns.length > 0;
    },

    url(path) {
      return "/nova-vendor/laravel-nova-csv-import/" + path;
    },

    addModifier(attribute) {
      if (Array.isArray(this.modifiers[attribute])) {
        this.modifiers[attribute].push({ name: "", settings: {} });
        return;
      }

      this.modifiers[attribute] = [{ name: "", settings: {} }];
    },
  },

  computed: {
    disabledSave() {
      return !this.hasValidConfiguration() || this.saving;
    },

    original_file_name() {
      if (this.config.original_filename?.includes(".")) {
        return this.config.original_filename.split(".").slice(0, -1).join(".");
      }

      return this.config.original_filename || "";
    },
  },
};
</script>
