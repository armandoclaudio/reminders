<template>
    <form :action="action" method="post" @submit.prevent="submit">
        <div class="flex flex-wrap md:flex-no-wrap justify-end">
            <div class="w-full md:w-1/2">
                <div class="mb-4 md:mb-0">
                    <input v-model="title" type="text" class="h-10 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{'border-red-500' : hasError('title')}" placeholder="Title" requiredsss autofocus>

                    <p v-if="hasError('title')" class="text-red-500 text-xs italic mt-4">
                        {{ getError('title') }}
                    </p>
                </div>
            </div>
            <div class="flex-1 md:ml-4">
                <div class="mb-4 sm:mb-0">
                    <label for="date" class="block md:hidden text-gray-700 text-sm font-bold mb-2">
                        Date:
                    </label>

                    <input v-model="date" type="date" class="bg-white h-10 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{'border-red-500' : hasError('date')}" placeholder="Date" requiredsss>

                    <p v-if="hasError('date')" class="text-red-500 text-xs italic mt-4">
                        {{ getError('date') }}
                    </p>
                </div>
            </div>
            <div class="flex-1 ml-4">
                <div class="mb-4 sm:mb-0">
                    <label for="time" class="block md:hidden text-gray-700 text-sm font-bold mb-2">
                        Time:
                    </label>

                    <input v-model="time" type="time" class="bg-white h-10 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{'border-red-500' : hasError('time')}" placeholder="Time" requiredsss>

                    <p v-if="hasError('time')" class="text-red-500 text-xs italic mt-4">
                        {{ getError('time') }}
                    </p>
                </div>
            </div>
            <div class="w-full sm:w-auto sm:ml-4 text-right flex items-end">
                <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-700 text-gray-100 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <span v-if="id == null">Create</span>
                    <span v-else>Update</span>
                </button>

                <button v-if="id != null" @click="clear" type="button" class="h-10 text-gray-900 py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Cancel
                </button>
            </div>
        </div>
    </form>
</template>

<script>
    export default {
        props: ['action', 'method'],

        data() {
            return {
                id: null,
                title: '',
                date: '',
                time: '',
                errors: {},
            }
        },

        mounted() {
            Events.$on('reminders:edit', this.editReminder)
        },

        methods: {
            editReminder(reminder) {
                this.id = reminder.id
                this.title = reminder.title
                this.date = dayjs(reminder.due_at).format('YYYY-MM-DD')
                this.time = dayjs(reminder.due_at).format('HH:mm')
            },

            submit() {
                if(this.id == null) {
                    axios.post(this.action, this.$data).then(({ data }) => {
                        Events.$emit('reminders:created', data)
                        this.clear()
                    }).catch(error => {
                        this.errors = error.response.data.errors
                    })

                    return
                }

                axios.patch(`${this.action}/${this.id}`, this.$data).then(({ data }) => {
                    Events.$emit('reminders:updated', data)
                    this.clear()
                }).catch(error => {
                    this.errors = error.response.data.errors
                })
            },

            clear() {
                this.id = null
                this.title = ''
                this.date = ''
                this.time = ''
                this.errors = {}
            },

            hasError(field) {
                return this.errors.hasOwnProperty(field)
            },

            getError(field) {
                return this.errors[field][0]
            }
        }
    }
</script>
