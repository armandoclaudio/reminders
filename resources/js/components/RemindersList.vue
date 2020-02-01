<template>
    <div v-if="reminders.length > 0" class="container mx-auto px-4 mt-12">
        <h2 class="font-semibold text-sm uppercase tracking-wide">Scheduled Reminders</h2>
        <div v-for="(date, i) in dates" :key="i" class="mt-8">
            <div class="font-semibold md:mb-2" v-text="dateFormat(date)"></div>
            <div v-for="reminder in dateReminders(date)" :key="reminder.id" class="flex items-baseline md:pl-4 py-2 md:hover:bg-blue-200">
                <div class="font-mono uppercase tracking-wide text-xs text-blue-700" v-text="timeFormat(reminder.due_at)"></div>
                <div class="ml-4" v-text="reminder.title"></div>
                <div class="ml-4 lowercase text-xs" v-if="reminder.repeats != null">
                    (every {{ reminder.repeats }})
                </div>
                <a class="ml-4 lowercase text-xs text-blue-600 hover:text-blue-800" href="#" @click="editReminder(reminder)">Edit</a>
                <a class="ml-4 lowercase text-xs text-red-600 hover:text-red-800" href="#" @click="deleteReminder(reminder.id)">Delete</a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['url', 'delete_url'],

        data() {
            return {
                reminders: [],
            }
        },

        mounted() {
            Events.$on('reminders:created', this.reminderCreated)
            Events.$on('reminders:updated', this.reminderUpdated)
            this.getReminders()
        },

        computed: {
            dates() {
                return _.uniq(
                    this.reminders.map(reminder => {
                        return dayjs(reminder.due_at).format('YYYY-MM-DD')
                    })
                ).sort()
            },
        },

        methods: {
            getReminders() {
                axios.get(this.url).then(({ data }) => {
                    this.reminders = data.reminders
                })
            },

            dateReminders(date) {
                return this.reminders.filter(reminder => {
                    return dayjs(reminder.due_at).format('YYYY-MM-DD') == date
                }).sort((a, b) => {
                    if(dayjs(a.due_at).isBefore(dayjs(b.due_at))) {
                        return -1
                    }
                    if(dayjs(a.due_at).isAfter(dayjs(b.due_at))) {
                        return 1
                    }
                    return 0
                })
            },

            dateFormat(date) {
                return dayjs(date).format('dddd, MMMM DD YYYY')
            },

            timeFormat(time) {
                return dayjs(time).format('hh:mma')
            },

            reminderCreated(reminder) {
                this.reminders.push(reminder)
            },

            reminderUpdated(reminder) {
                let index = this.reminders.findIndex(arrayReminder => {
                    return arrayReminder.id == reminder.id
                })

                if(index == -1) return this.getReminders()

                this.reminders.splice(index, 1, reminder)
            },

            editReminder(reminder) {
                Events.$emit('reminders:edit', reminder)
            },

            deleteReminder(id) {
                axios.delete(`${this.url}/${id}`).then(({ data }) => {
                    if(! data.success) return

                    let index = this.reminders.findIndex(reminder => {
                        return reminder.id == id
                    })

                    if(index == -1) return this.getReminders()

                    this.reminders.splice(index, 1)
                })
            }
        }
    }
</script>
