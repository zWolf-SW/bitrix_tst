import { Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { mapGetters, mapState } from 'ui.vue3.vuex';
import { ServiceLocator } from '../service/service-locator';
import { Header } from './header';
import { SearchBox } from './searchbox';
import { Section } from './section';

export const Grid = {
	name: 'Grid',
	components: { Section, Header, SearchBox },
	loader: null,
	computed: {
		...mapState({
			isSaving: (state) => state.application.isSaving,
			guid: (state) => state.application.guid,
			searchContainerSelector: (state) => state.application.options.searchContainerSelector,
		}),
		...mapGetters({
			shownSections: 'accessRights/shown',
			shownUserGroups: 'userGroups/shown',
		}),
	},
	mounted()
	{
		ServiceLocator.getHint(this.guid).initOwnerDocument(this.$refs.container);
		EventEmitter.subscribe('BX.UI.AccessRights.V2:addRight', this.addRight);
		EventEmitter.subscribe('BX.UI.AccessRights.V2:updateRightTitle', this.updateRightTitle);
		EventEmitter.subscribe('BX.UI.AccessRights.V2:updateRightSubtitle', this.updateRightSubtitle);
		EventEmitter.subscribe('BX.UI.AccessRights.V2:markRightAsModified', this.markRightAsModified);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe('BX.UI.AccessRights.V2:addRight', this.addRight);
		EventEmitter.unsubscribe('BX.UI.AccessRights.V2:updateRightTitle', this.updateRightTitle);
		EventEmitter.unsubscribe('BX.UI.AccessRights.V2:updateRightSubtitle', this.updateRightSubtitle);
		EventEmitter.unsubscribe('BX.UI.AccessRights.V2:markRightAsModified', this.markRightAsModified);
	},
	methods: {
		scrollToSection(sectionCode: string) {
			const section = this.$refs.sections.find((item) => item.code === sectionCode);
			if (section)
			{
				scrollTo({
					top: Dom.getPosition(section.$el).top - 155,
					behavior: 'smooth',
				});
			}
		},
		addRight(event: BaseEvent) {
			const { guid, sectionCode, right } = event.data;
			if (!guid)
			{
				console.warn('ui.accessrights.v2: addRight: application guid should be passed in event data');

				return;
			}

			if (guid === this.$store.getters['application/guid'])
			{
				this.$store.dispatch('accessRights/addRight', { sectionCode, right });
			}
		},
		updateRightTitle(event: BaseEvent) {
			const { guid, sectionCode, rightId, rightTitle } = event.data;
			if (!guid)
			{
				console.warn('ui.accessrights.v2: updateRightTitle: application guid should be passed in event data');

				return;
			}

			if (guid === this.$store.getters['application/guid'])
			{
				this.$store.dispatch('accessRights/updateRightTitle', { sectionCode, rightId, rightTitle });
			}
		},
		updateRightSubtitle(event: BaseEvent) {
			const { guid, sectionCode, rightId, rightSubtitle } = event.data;
			if (!guid)
			{
				console.warn('ui.accessrights.v2: updateRightSubtitle: application guid should be passed in event data');

				return;
			}

			if (guid === this.$store.getters['application/guid'])
			{
				this.$store.dispatch('accessRights/updateRightSubtitle', { sectionCode, rightId, rightSubtitle });
			}
		},
		markRightAsModified(event: BaseEvent) {
			const { guid, sectionCode, rightId, isModified } = event.data;
			if (!guid)
			{
				console.warn('ui.accessrights.v2: markRightAsModified: application guid should be passed in event data');

				return;
			}

			if (guid === this.$store.getters['application/guid'])
			{
				this.$store.dispatch('accessRights/markRightAsModified', { sectionCode, rightId, isModified });
			}
		},
	},
	template: `
		<Teleport v-if="searchContainerSelector" :to="searchContainerSelector">
			<SearchBox/>
		</Teleport>
		<div ref="container" class='ui-access-rights-v2' :class="{
			'ui-access-rights-v2-block': isSaving,
		}">
			<Header :user-groups="shownUserGroups"/>
			<Section
				v-for="[sectionCode, accessRightSection] in shownSections"
				:key="sectionCode"
				:code="accessRightSection.sectionCode"
				:is-expanded="accessRightSection.isExpanded"
				:title="accessRightSection.sectionTitle"
				:sub-title="accessRightSection.sectionSubTitle"
				:hint="accessRightSection.sectionHint"
				:icon="accessRightSection.sectionIcon"
				:rights="accessRightSection.rights"
				:action="accessRightSection.action"
				:user-groups="shownUserGroups"
				ref="sections"
			/>
		</div>
	`,
};
