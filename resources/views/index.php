<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
</head>
<body>
<div id="app">
    <v-app id="inspire">
        <div>
            <v-app-bar
                color="indigo darken-3"
                dense
                dark
            >
                <v-toolbar-title>Link Grid</v-toolbar-title>

                <v-spacer></v-spacer>

                <v-btn icon @click="editMode = !editMode">
                    <v-icon v-if="!editMode">mdi-pencil</v-icon>
                    <v-icon v-if="editMode">mdi-eye</v-icon>
                </v-btn>


                </v-menu>
            </v-app-bar>
            <v-container>
                <v-row class="align-stretch" style="height: 90vh">
                    <v-col
                        v-for="card in cards"
                        :key="card.title"

                        class="align-stretch col-md-4 col-12"
                        :class="`d-flex align-stretch`"
                    >
                        <v-card dark :style="{backgroundColor: card.color ?? '#ccd1d9'}" style="width: 100%" class="d-flex flex-column pb-5">

                            <v-card-title v-text="card.title" class="justify-center mb-auto" style="font-size: 2.5em"></v-card-title>

                            <v-card-actions class="justify-center">
                                <v-btn v-if="card.title && !editMode" x-large outlined color="white" :href="card.link" target="_blank">
                                    <v-icon left>mdi-open-in-new</v-icon> Visit
                                </v-btn>
                                <v-btn v-if="!card.title" x-large outlined color="white" @click="openEditModal(card)">
                                    <v-icon left>mdi-plus-circle-outline</v-icon> Create
                                </v-btn>
                                <v-btn v-if="editMode && card.title" x-large outlined color="white" @click="openEditModal(card)">
                                    <v-icon left>mdi-pencil-outline</v-icon> Edit
                                </v-btn>
                                <v-btn v-if="editMode && card.title" x-large outlined color="white" @click="openDeleteModal(card)">
                                    <v-icon left>mdi-delete-outline</v-icon> Delete
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>


            <v-dialog v-model="editModal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline">Link tab</span>
                    </v-card-title>
                    <v-card-text>
                        <v-form
                            ref="form"
                            v-model="valid"
                            lazy-validation
                        >
                        <v-container>
                            <v-row>

                                <v-col cols="12">
                                    <v-text-field label="Title*" required :rules="[v => !!v || 'Title is required!']" v-model="currentCard.title"></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field label="Link*" required :rules="urlRules" v-model="currentCard.link"></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        :items="colors"
                                        label="Color*"
                                        v-model="currentCard.colorName"
                                        :rules="[v => !!v || 'Color is required']"
                                        @change="onColorChange($event)"
                                        required
                                    ></v-select>
                                </v-col>

                            </v-row>
                        </v-container>
                        </v-form>
                        <small>*indicates required field</small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="closeEditModal()">Close</v-btn>
                        <v-btn color="blue darken-1" text @click="onSubmit()" :loading="submitLoading"
                               :disabled="submitLoading">Save</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog v-model="deleteModal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline">Are you sure you want to delete the "{{ currentCard.title }}" tab?</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>

                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="deleteModal = false">No</v-btn>
                        <v-btn color="red darken-1" text @click="onDelete()" :loading="deleteLoading"
                               :disabled="deleteLoading">Yes, Delete!</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

        </div>

        <v-overlay :value="overlay">
            <v-progress-circular indeterminate size="64"></v-progress-circular>
        </v-overlay>
    </v-app>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<!--    Vue resource HTTP-->
<script src="https://cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
<script>
    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return decodeURIComponent(c.substring(name.length, c.length));
            }
        }
        return "";
    }

    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data: () => ({
            cards: [],
            colors: [
                'Select Color',
            ],
            colorMap: {},
            editMode: false,
            currentCard: { id: null, title: null, color: null, link: null, colorName: null},
            currentCardBeforeEdit: null,
            urlRules: [
                v => !!v || 'Link is required',
                v => /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/.test(v) || 'Link must be valid',
            ],
            editModal: false,
            deleteModal: false,
            submitLoading: false,
            deleteLoading: false,
            overlay: true,
            valid: true,
        }),
        methods: {
            openEditModal(card) {
                this.currentCard = card;
                this.currentCardBeforeEdit = JSON.parse(JSON.stringify(card));
                this.editModal = true;
            },
            closeEditModal() {
                this.cards[this.currentCard.id - 1] = JSON.parse(JSON.stringify(this.currentCardBeforeEdit));
                this.editModal = false;
                this.$refs.form.resetValidation();
            },
            openDeleteModal(card) {
                this.currentCard = card;
                this.deleteModal = true;
            },
            onSubmit() {
                if (!this.$refs.form.validate()) {
                    return false;
                }

                this.submitLoading = true;
                this.$http.post('/grid', this.currentCard, {headers: {'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')}}).then(response => {
                    // success callback
                    // let links = response.body.links;
                    // for (let i = 0; i < links.length; i++) {
                    //     Vue.set(this.cards, links[i].id - 1, links[i]);
                    // }

                    this.$refs.form.resetValidation();
                    this.editModal = false;
                    this.submitLoading = false;
                }, response => {
                    this.submitLoading = false;
                    // error callback
                });
            },
            onDelete() {
                this.deleteLoading = true;
                this.$http.delete('/grid/' + this.currentCard.id, {headers: {'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')}}).then(response => {
                    // success callback

                    this.currentCard.title = null;
                    this.currentCard.color = null;
                    this.currentCard.link = null;
                    this.currentCard.colorName = null;

                    this.deleteModal = false;
                    this.deleteLoading = false;
                }, response => {
                    this.deleteLoading = false;
                    // error callback
                });
            },
            onColorChange(value) {
                this.currentCard.color = this.colorMap[value];
            }
        },
        mounted: function() {
            for (let i = 1; i <= 9; i++) {
                this.cards.push({ id: i, title: null, color: null, link: null, colorName: null});
            }

            this.$http.get('/grid').then(response => {
                // success callback
                let links = response.body.links;
                for (let i = 0; i < links.length; i++) {
                    // this.cards.$set(links[i].id - 1, links[i]);
                    Vue.set(this.cards, links[i].id - 1, links[i]);
                }

                let colors = response.body.colors;
                console.log(colors);
                for (let i = 0; i < colors.length; i++) {
                    Vue.set(this.colors, colors[i].id, colors[i].name);
                    Vue.set(this.colorMap, colors[i].name, colors[i].hex);
                }
                this.overlay = false;
            }, response => {
                // error callback
            });
        }
    })
</script>
</body>
</html>
