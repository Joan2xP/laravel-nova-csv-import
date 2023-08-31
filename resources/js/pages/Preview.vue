<template>
    <div>
        <Head>
            <title>Vista prèvia de l'import</title>
        </Head>

        <heading class="mb-6">Vista prèvia de l'import</heading>

        <card class="flex flex-col" style="min-height: 300px">
            <div class="p-8 space-y-4">
                <p>
                    Has seleccionat importar <b>{{ mapped_columns.length }}</b> camp(s) des de <b>{{ total_rows }}</b>
                    entrade(s) en total, en el teu <b>{{ resource }}</b> recurs. El següent és una mostra de com es veuran 
                    les dades un cop importades.
                </p>

                <div class="overflow-scroll">
                    <table cellpadding="10">
                        <thead class="border-b">
                            <tr>
                                <th class="border-r" rowspan="3" valign="bottom">#</th>
                                <th v-for="(column, field) in columns">
                                    {{ column }}
                                </th>
                            </tr>
                            <tr>
                                <th v-for="(column, field) in columns">
                                    <i v-if="! column">sense mapejar</i>
                                    <span v-else>&downarrow;</span>
                                </th>
                            </tr>
                            <tr>
                                <th v-for="(column, field) in columns">{{ field }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in rows" class="group">
                                <td class="text-right border-r group-hover:bg-gray-50">{{ index + 1 }}</td>
                                <td v-for="(column, field) in columns" class="group-hover:bg-gray-50">
                                    <code>
                                        {{ row[field] }}
                                        <i v-if="! row[field]">res</i>
                                    </code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center space-x-2">
                    <LinkButton @click="reconfigure"><HeroiconsOutlineRewind /> Reconfigurar</LinkButton>
                    
                    <DefaultButton :disabled="importing" @click="runImport" ref="import">
                        {{ importing ? 'Important...' : 'Importar &rightarrow;' }}
                    </DefaultButton>
                </div>
            </div>
        </card>
    </div>
</template>

<script>
export default {
    data() {
        return {
            importing: false,
        };
    },

    props: [
        'columns',
        'mapped_columns',
        'resource',
        'file',
        'total_rows',
        'rows',
    ],

    methods: {
        runImport() {
            this.importing = true;

            let data = {
                file: this.file
            };

            Nova.request()
                .post(this.url('import'), data)
                .then((response) => {
                    if (response.status === 200) {
                        Nova.success('Importing...');
                        Nova.visit('/csv-import/review/' + this.file);
                    }
                })
                .catch((e) => {
                    this.importing = false;
                    Nova.error('Han hagur problemes en algunes de les teves dades');
                });

            this.importing = false;
        },

        reconfigure() {
            Nova.visit('/csv-import/configure/' + this.file);
        },

        url: function (path) {
            return '/nova-vendor/laravel-nova-csv-import/' + path;
        }
    },
}
</script>
