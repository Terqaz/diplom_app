<template>
	<FormHeader label="Настройка подключений бота к социальным сетям" entityType="Бот"
				:entityName="botTitle" :backUrl="backUrl" />
	<hr>

	<div class="container mx-auto" style="max-width:550px;">
		<div v-for="(config, code) in configs" :key="code">
			<ConnectionEdit :code="code"
							v-model:connectionId="config.connectionId"
							v-model:accessToken="config.accessToken"
							v-model:isEnabled="config.isEnabled" />
		</div>

		<button @click="submit" class="btn btn-sm btn-primary me-3">
			Сохранить
		</button>
	</div>
</template>

<script>
import FormHeader from '../Form/FormHeader.vue';
import ConnectionEdit from './ConnectionEdit.vue';
import { ROUTES } from '../../js/routes';
import { DEFAULT_SOCIAL_NETWORK_CONFIG } from '../../js/defaultEntities';

export default {
	name: 'BotConnectionsEdit',
	components: { FormHeader, ConnectionEdit },
	props: {
		botId: {
			type: Number,
			required: true
		},
		botTitle: {
			type: String,
			required: true
		},
		configsData: {
			type: Object,
			required: true
		},
		typesCatalogs: {
			type: Array,
			required: true
		}
	},

	data() {
		return {
			backUrl: ROUTES.app_bot_show(this.botId),
			configs: {},
		}
	},

	created() {
		sessionStorage.setItem('socialNetworkCodes', JSON.stringify(this.typesCatalogs.socialNetworkCodes));

		if (Object.keys(this.configsData).length > 0) {
			this.configs = structuredClone(this.configsData);
		}

		for (const code in this.typesCatalogs.socialNetworkCodes) {
			if (!this.configs[code]) {
				const newConfig = structuredClone(DEFAULT_SOCIAL_NETWORK_CONFIG);
				newConfig.code = code;

				this.configs[code] = newConfig;
			}
		}
	},

	methods: {
		submit() {
			const body = [];

			for (const code in this.configs) {
				const c = this.configs[code];

				if (c.connectionId && c.accessToken) {
					body.push(c);
				}
			}

			fetch(ROUTES.app_bot_connections_edit(this.botId), {
				method: 'POST',
				body: JSON.stringify(body),
				headers: { 'Content-Type': 'application/json' }
			})
				.then((response) => {
					if (response.redirected) {
						window.location.href = response.url;
						return;
					}

					const data = response.json();
					if (data.status === false) {
						this.testErrorMessage = "Не удалось подключиться с данным токеном доступа";
					}
					this.connectionTestStatus = data.status ? 'success' : 'failed';
				});
		}
	},
}
</script>

<style lang="css" scoped></style>