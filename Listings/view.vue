<template>
	<Page actionBarHidden="true" class="page-interface lora-sensors" enableSwipeBackNavigation="false">
		<GridLayout iosOverflowSafeArea="true">
			
			<GridLayout rows="*, auto, auto, *" paddingLeft="20" paddingRight="20" paddingTop="30" paddingBottom="100" height="100%" v-if="dataItems.length === 0 && !loading">
				<NoDataIcon marginBottom="40" verticalAlignment="bottom" horizontalAlignment="center" row="1" />
				<HTMLLabel class="marginal-text" horizontalAlignment="center" textAlignment="center" width="66%" textWrap="true" row="2" text="Es wurden keine passenden Einträge gefunden." />
			</GridLayout>
			
			<PullToRefresh @refresh="refresh" indicatorColor="transparent" indicatorFillColor="transparent">
				<CollectionView :items="dataItems" paddingLeft="20" paddingRight="20" paddingTop="30" paddingBottom="100" :itemTemplateSelector="templateSelector" :scrollBarIndicatorVisible="false" @loadMoreItems="loadMore">
					<v-template name="default">
						<GridLayout columns="20, 30, 30, *, 30, 20" rows="40, *, 3, 30" class="list-template" @tap="goToDetails(item)">
							<HTMLLabel :text="getIconForSensorType(item.lora_sensor_type_id)" class="mdi-outlined-18px desc-icon" col="1" row="0" verticalAlignment="top" />
							<HTMLLabel :text="item.name" col="2" colSpan="2" row="0" textWrap="true" verticalAlignment="top" class="desc-text" />
							<HTMLLabel :text="item['data'][0]['data']" class="data-text" row="1" col="1" colSpan="3" lineBreak="end" maxLines="1" />
							<Separator col="0" colSpan="6" row="2" marginLeft="0" marginRight="0" />
							<CanvasView col="1" row="4" width="18" height="8" @draw="onDraw($event, item.status)" horizontalAlignment="left" marginLeft="2" :key="item.id" />
							<HTMLLabel text="visibility" class="visibility mdi-outlined-12px" col="2" row="4" :color="item.status.lastSeenCritical ? '#C15B5B' : '#7BA578'" textAlignment="center" />
							<HTMLLabel :text="item.lora_device.last_seen_at ? parseDateTimeToPattern(item.lora_device.last_seen_at) + ' Uhr' : 'keine Daten vorhanden.'" col="3" row="4" verticalAlignment="center" verticalTextAlignment="center" :color="item.status.lastSeenCritical ? '#C15B5B' : '#7BA578'" height="100%" />
							<HTMLLabel row="0" col="4" text="arrow_forward_ios" class="proceed-icon mdi-outlined-18px" verticalAlignment="top" horizontalAlignment="right" />
						</GridLayout>
					</v-template>
					
					<v-template name="skeleton">
						<LoraListSkeleton :single="true" />
					</v-template>
				</CollectionView>
			</PullToRefresh>
		</GridLayout>
	</Page>
</template>

<script>
import { navigateToFunction } from "~/utils/helpers/navigationFunctions";
import LORASensorDetailView from "@/utils/_microapps/_lorawan/sensors/LORASensorDetailView.vue";
import MappingMixin from "../mixins/mapping";
import Separator from "@/utils/_ground/shared/Separator.vue";
import { parseDateTimeToPattern } from "@/utils/helpers/functions";
import LoraListSkeleton from "~/utils/_microapps/_lorawan/LoraListSkeleton.vue";
import paginationMixin from "../mixins/pagination";
import { drawBattery } from "@/utils/_microapps/_lorawan/utils/canvas";
import NoDataIcon from "~/static/NoDataIcon.vue";

export default {
	components: {
		LoraListSkeleton,
		NoDataIcon,
		LORASensorDetailView,
		MappingMixin,
		Separator,
	},
	props: ["divisionTitle"],
	name: "LORASensors",
	mixins: [paginationMixin],
	data() {
		return {
			rowsPerPage: 25,
			MappingMixin,
		};
	},
	computed: {
		url() {
			return "api/lora/sensors/search";
		},
		params() {
			return {
				page: this.page,
				rowsPerPage: this.rowsPerPage,
			};
		},
		dataItems() {
			if (this.loading) {
				return [{ type: "skeleton" }, { type: "skeleton" }];
			}
			return this.items;
		},
	},
	methods: {
		parseDateTimeToPattern,
		onDraw(event, status) {
			drawBattery(event, status);
		},
		templateSelector(item) {
			if (item.type) {
				return item.type;
			}
			return "default";
		},
		goToDetails(item) {
			let frame = "apps-frame";
			let options = {
				frame: frame,
				transition: "slideLeft",
				props: {
					item: item,
				},
			};
			let targetSettings = {
				hideFooter: true,
				setPageTitle: item.name,
			};
			navigateToFunction(this, LORASensorDetailView, options, targetSettings);
		},
		getIconForSensorType(sensorType) {
			return MappingMixin.computed.iconsMapping()[sensorType]
		},
	},
	mounted() {
		this.paginate();
	},
};
</script>
