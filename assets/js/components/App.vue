<template>
    <div>
        <h2 class="center">Todo list</h2>
        <div>
            <b-table striped hover :items="items">
                <template #cell(id)="row">
                    {{ row.id }}
                </template>

            </b-table>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                ready: false,
                items: null
            }
        },
        mounted() {
            this.axios
                .get('/api/task_list/list')
                .then(response => {
                    console.log(response.data)
                    const items = []
                    response.data.forEach(list => {
                        console.log(list)
                        console.log(list.createdAt.timestamp)
                        items.push({
                            id: list.id,
                            name: list.name,
                            tasks: list.tasks.length,
                            created: new Date(list.createdAt.timestamp * 1000).toLocaleDateString("nl-NL")+' '+new Date(list.createdAt.timestamp * 1000).toLocaleTimeString("nl-NL"),
                            updated: new Date(list.updatedAt.timestamp * 1000).toLocaleDateString("nl-NL")+' '+new Date(list.updatedAt.timestamp * 1000).toLocaleTimeString("nl-NL")
                        })
                    })
                    this.items = items
                    this.ready = true
                })
        }
    };
</script>

<style>
    .center {
        text-align: center;
    }
</style>