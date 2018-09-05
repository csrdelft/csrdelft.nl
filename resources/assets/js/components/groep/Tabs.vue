<template>
	<div>
		<nav>
			<div role="tablist" class="nav nav-tabs">
				<a
					v-for="(tab, i) in tabs"
					:key="i"
					v-show="tab.isVisible"
					:href="tab.hash"
					:class="{'nav-item nav-link': true, 'active': tab.isActive, 'disabled': tab.isDisabled }"
					class="nav-item nav-link"
					role="tab"
					@click="selectTab(tab.hash, $event)"
				>
					<i :class="tab.icon"></i>
				</a>
			</div>
		</nav>
		<div class="tabs-component-panels">
			<slot/>
		</div>
	</div>
</template>

<script>
	export default {
		props: {
			cacheLifetime: {
				default: 5,
			},
			options: {
				type: Object,
				required: false,
				default: () => ({
					useUrlFragment: true,
				}),
			},
		},
		data: () => ({
			tabs: [],
			activeTabHash: '',
		}),
		computed: {
			storageKey() {
				return `vue-tabs-component.cache.${window.location.host}${window.location.pathname}`;
			},
		},
		created() {
			this.tabs = this.$children;
		},
		mounted() {
			window.addEventListener('hashchange', () => this.selectTab(window.location.hash));
			if (this.findTab(window.location.hash)) {
				this.selectTab(window.location.hash);
				return;
			}

			if (this.tabs.length) {
				this.selectTab(this.tabs[0].hash);
			}
		},
		methods: {
			findTab(hash) {
				return this.tabs.find(tab => tab.hash === hash);
			},
			selectTab(selectedTabHash, event) {
				// See if we should store the hash in the url fragment.
				if (event && !this.options.useUrlFragment) {
					event.preventDefault();
				}
				const selectedTab = this.findTab(selectedTabHash);
				if (!selectedTab) {
					return;
				}
				if (selectedTab.isDisabled) {
					return;
				}
				this.tabs.forEach(tab => {
					tab.isActive = (tab.hash === selectedTab.hash);
				});
				this.$emit('changed', {tab: selectedTab});
				this.activeTabHash = selectedTab.hash;
			},
			setTabVisible(hash, visible) {
				const tab = this.findTab(hash);
				if (!tab) {
					return;
				}
				tab.isVisible = visible;
				if (tab.isActive) {
					// If tab is active, set a different one as active.
					tab.isActive = visible;
					this.tabs.every((tab, index, array) => {
						if (tab.isVisible) {
							tab.isActive = true;
							return false;
						}
						return true;
					});
				}
			},
		},
	};
</script>

<style scoped>

</style>
