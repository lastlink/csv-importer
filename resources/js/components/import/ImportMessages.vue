<!--
  - ImportMessages.vue
  - Copyright (c) 2019 - 2019 thegrumpydictator@gmail.com
  -
  - This file is part of the Firefly III CSV importer
  - (https://github.com/firefly-iii-csv-importer).
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <https://www.gnu.org/licenses/>.
  -->

<template>
    <div v-if="this.errors !== {} || this.warnings !== {} || this.messages !== {}">
        <p><br />
            During the import, messages, warnings and errors may appear. They are listed here.
        </p>
        <div v-if="this.errors !== {}">
            <strong class="text-danger">Error(s)</strong>
            <ul>
                <li v-for="(errorList, line) in this.errors">Line #{{ line }}:
                    <ul>
                        <li v-for="(error) in errorList">{{ error }}</li>
                    </ul>
                </li>
            </ul>
        </div>
        <div v-if="this.warnings !== {}">
            <strong class="text-warning">Warning(s)</strong>
            <ul>
                <li v-for="(warningList, line) in this.warnings">Line #{{ line }}:
                    <ul>
                        <li v-for="(warning) in warningList">{{ warning }}</li>
                    </ul>
                </li>
            </ul>
        </div>
        <div v-if="this.messages !== {}">
            <strong class="text-info">Message(s)</strong>
            <ul>
                <li v-for="(messageList, line) in this.messages">Line #{{ line }}:
                    <ul>
                        <li v-for="(message) in messageList">{{ message }}</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ImportMessages",
        props: {
            messages: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            warnings: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            errors: {
                type: Object,
                default: function () {
                    return {};
                }
            },
        },
    }
</script>

<style scoped>

</style>