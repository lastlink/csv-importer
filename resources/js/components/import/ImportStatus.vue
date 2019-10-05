<!--
  - ImportStatus.vue
  - Copyright (c) 2019 thegrumpydictator@gmail.com
  -
  - This file is part of Firefly III CSV Importer.
  -
  - Firefly III CSV Importer is free software: you can redistribute it and/or
  - modify it under the terms of the GNU General Public License as published
  - by the Free Software Foundation, either version 3 of the License, or
  - (at your option) any later version.
  -
  - Firefly III CSV Importer is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Firefly III CSV Importer.If not, see
  - <http://www.gnu.org/licenses/>.
  -->

<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">Import status</div>
                <div class="card-body" v-if="'waiting_to_start' === this.status && false === this.triedToStart">
                    <p>Here is a link to your config. Press start.</p>
                    <p>
                        <button
                            class="btn btn-success"
                            v-on:click="callStart" type="button">Start job
                        </button>
                    </p>
                </div>
                <div class="card-body" v-if="'waiting_to_start' === this.status && true === this.triedToStart">
                    <p>Waiting for the job to start..
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ImportStatus",
        /*
    * The component's data.
    */
        data() {
            return {
                triedToStart: false,
                status: ''
            };
        },
        props: [],
        mounted() {
            console.log(`Mounted, check job at ${jobStatusUri}.`);
            this.getJobStatus();
        },
        methods: {
            getJobStatus: function () {
                console.log('getJobStatus');
                axios.get(jobStatusUri).then((response) => {
                    // handle success
                    this.status = response.data.status;
                    console.log(`Job status is ${this.status}.`);
                    if (false === this.triedToStart && 'waiting_to_start' === this.status) {
                        // call to job start.
                        console.log('Job hasnt started yet. Show user some info');
                        return;
                    }
                    if (true === this.triedToStart && 'waiting_to_start' === this.status) {
                        console.log('Job hasnt started yet, but wont try again.');
                    }

                    setTimeout(function () {
                        this.getJobStatus();
                    }.bind(this), 2000)
                });
            },
            callStart: function () {
                console.log('Call job start URI: ' + jobStartUri);
                axios.post(jobStartUri);
                this.triedToStart = true;
            },
        },
        watch: {}
    }
</script>

<style scoped>

</style>
