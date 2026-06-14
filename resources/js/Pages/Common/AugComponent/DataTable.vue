<script setup>
import { useLayout } from '@/layout/composables/layout';
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import ContextMenu from 'primevue/contextmenu'; // Import ContextMenu
import DataTable from 'primevue/datatable';
import Menu from 'primevue/menu';
import Row from 'primevue/row';
import TieredMenu from 'primevue/tieredmenu';
import ProgressSpinner from 'primevue/progressspinner';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import {
    onMounted,
    onBeforeUnmount,
    ref,
    watch,
    useSlots,
    computed,
    nextTick,
} from 'vue';
//import MasterStatusActiveDeActive from '../AugComponent/MasterStatusActiveDeActive.vue';

const props = defineProps([
    'permissions',
    'allColumns',
    'route_name',
    'moduleNm',
    'menuItems',
    'checkboxMenu',
    'showSelection',
    'route_param',
    'query_param',
    'showActionButton',
    'rowClassFn',
    'columnGroups',
    'footerTotals',
    'initialFilters',
    'defaultPageSize',
]);

// Set default values for props
const { showActionButton = true } = props;

const emit = defineEmits([
    'shortcut-key',
    'body-emit-value',
    'dataUpdate',
    'filters-change',
]);

const toast = useToast();
const { isHorizontal } = useLayout();
const dt = ref();
const products = ref();
const selectedProducts = ref();
const filters = ref({});
const size = ref(
    Number.isFinite(Number(props.defaultPageSize))
        ? Math.max(1, Number(props.defaultPageSize))
        : 50,
); // per page size
const page = ref(1); // page no for pagination
const totalRecords = ref(0);
const sortField = ref('id');
const sortOrder = ref(0);
const loading = ref(false);
const contextSelect = ref(null);
const columns_rearrange = ref('');
const form = ref(null);
const show = ref(null);
const delete_dialog_ref = ref(null);
const firstLoading = ref(false);
const toggleState = ref(false);
const columnSelections = ref({});
const columnMenuRefs = ref({});
const filterDebounce = ref(null);
const filterFetchPending = ref(false);
const lastFocusedFilterId = ref(null);
const lastFocusedFilterEl = ref(null);

const slots = useSlots();
const hasColumnGroup = ref(!!slots.columnGroup || !!props.columnGroups);

const shouldRenderColumnGroup = computed(() => {
    if (!props.columnGroups || props.columnGroups.length === 0) return false;
    return props.columnGroups[0].some((col) => col.colspan || col.rowspan);
});

const sanitizeFilters = (value) => {
    if (!value || typeof value !== 'object') {
        return {};
    }

    return Object.entries(value).reduce((acc, [key, entry]) => {
        if (entry === null || entry === undefined) {
            return acc;
        }

        if (Array.isArray(entry)) {
            acc[key] = entry.filter((item) => item !== null && item !== undefined);
            return acc;
        }

        if (typeof entry === 'object') {
            const clone = { ...entry };
            if (clone.operator === null || clone.operator === undefined) {
                delete clone.operator;
            }
            if (Array.isArray(clone.constraints)) {
                clone.constraints = clone.constraints.filter(
                    (constraint) => constraint !== null && constraint !== undefined,
                );
            }
            acc[key] = clone;
            return acc;
        }

        acc[key] = entry;
        return acc;
    }, {});
};

const primevueFilters = computed(() => sanitizeFilters(filters.value));

const buildQueryString = (params = {}) => {
    const query = new URLSearchParams();
    Object.entries(params || {}).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') {
            return;
        }

        if (Array.isArray(value)) {
            value.forEach((item) => {
                if (item !== undefined && item !== null && item !== '') {
                    query.append(key, item);
                }
            });
            return;
        }

        query.set(key, value);
    });

    const queryString = query.toString();
    return queryString ? `?${queryString}` : '';
};

const toCamelCase = (value = '') => {
    return value.replace(/-([a-zA-Z0-9])/g, (_, char) => char.toUpperCase());
};

const getFilterModelPropName = (column) => {
    const emit = column?.filter_components?.emit;
    if (!emit?.startsWith('update:')) {
        return null;
    }

    return toCamelCase(emit.replace('update:', ''));
};

const resolveFilterParams = (column) => {
    const params = resolveParams([], column.filter_components.param, {
        ...props.route_param,
        ...filters.value,
    });
    const modelPropName = getFilterModelPropName(column);

    const filterValue = filters.value?.[column.filterNm];
    if (
        modelPropName &&
        column.filterNm &&
        filterValue !== '' &&
        filterValue !== null &&
        filterValue !== undefined
    ) {
        params[modelPropName] = filterValue;
    }

    return params;
};

const resolveMenuItemUrl = (item, data, options = {}) => {
    const { ignoreAction = false } = options;
    const directUrl =
        typeof item.url === 'function' ? item.url(data) : item.url;
    if (directUrl) {
        return directUrl;
    }

    // Popup-driven editors already provide their own action handlers.
    if (!ignoreAction && (item.action || item.command)) {
        return null;
    }

    if (!data?.id || !props.route_name) {
        return null;
    }

    if (item.type !== 'edit' && item.type !== 'view_only') {
        return null;
    }

    const baseRouteName = String(props.route_name).replace(/\.index$/, '');
    if (!baseRouteName) {
        return null;
    }

    let url = route(`${baseRouteName}.edit`, data.id);
    const query = {
        ...(props.route_param || {}),
    };

    if (item.type === 'view_only') {
        query.view_type = 'only_view';
    }

    url += buildQueryString(query);
    return url;
};

const isMenuItemAllowed = (item, data) =>
    (!item.permission ||
        props.permissions[item.permission] ||
        item.permission_name) &&
    (!item.visible ||
        (typeof item.visible === 'function'
            ? item.visible(data)
            : item.visible));

const isModifierPressed = (event) =>
    !!(
        event?.ctrlKey ||
        event?.metaKey ||
        event?.originalEvent?.ctrlKey ||
        event?.originalEvent?.metaKey
    );

const triggerEditShortcut = (item, data, event) => {
    const menuUrl = resolveMenuItemUrl(item, data, { ignoreAction: true });
    if (menuUrl) {
        window.open(menuUrl, '_blank', 'noopener,noreferrer');
        return true;
    }

    if (typeof item.action !== 'function') {
        return false;
    }

    item.action(data, {
        ...event,
        ctrlKey: true,
        metaKey: true,
        originalEvent: {
            ...(event?.originalEvent ?? event ?? {}),
            ctrlKey: true,
            metaKey: true,
        },
    });
    return true;
};

const fetchData = async (from = null, filterData = {}) => {
    try {
        loading.value = true;
        if (from == 'mounted') {
            firstLoading.value = true;
        }

        if (filterData && Object.keys(filterData).length) {
            filters.value = {
                ...filters.value,
                ...filterData,
            };
            emit('filters-change', getCurrentFilters());
        }

        let route_name = '';
        if (
            props.query_param > 0 ||
            props.moduleNm == 'crm_account_contacts' ||
            props.moduleNm == 'inventory_account_contacts'
        ) {
            route_name = route(props.route_name, props.query_param);
        } else {
            route_name = route(props.route_name);
        }
        const todayFormatted = () => {
            const d = new Date();
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}-${month}-${year}`;
        };

        const applyPersistedDateFilters = (params) => {
            try {
                if (typeof window === 'undefined' || !window.location) {
                    return params;
                }

                const path = window.location.pathname;
                const today = todayFormatted();

                const tryRestore = (key) => {
                    const storageKey = `aug_datepicker:${path}:${key}`;
                    const raw = sessionStorage.getItem(storageKey);
                    if (!raw) return null;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
                    return JSON.parse(raw);
                };                                                                                                  

                if (
                    Object.prototype.hasOwnProperty.call(params, 'from_date') &&
                    params.from_date === today
                ) {
                    const persisted = tryRestore('from_date');
                    if (persisted) {
                        params.from_date = persisted;
                    }
                }

                if (
                    Object.prototype.hasOwnProperty.call(params, 'to_date') &&
                    params.to_date === today
                ) {
                    const persisted = tryRestore('to_date');
                    if (persisted) {
                        params.to_date = persisted;
                    }
                }
            } catch (e) {
                return params;
            }
            return params;
        }; 
        const response = await axios.get(route_name, {
            params: {
                size: size.value,
                page: page.value,
                sortField: sortField.value,
                sortOrder: sortOrder.value === 1 ? 'asc' : 'desc',
                // Default params first, then allow UI filters to override
                ...applyPersistedDateFilters({
                    ...props.route_param,
                    ...filters.value,
                    ...filterData,
                }),
            },
        });

        products.value = response.data.data;
        emit('dataUpdate', products.value);
        applyFooterTotals(products.value);
        page.value = response.data.current_page;
        size.value = response.data.per_page;
        totalRecords.value = response.data.total;
        loading.value = false;
        if (from == 'mounted') {
            firstLoading.value = false;
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to fetch data',
            life: 3000,
        });
    } finally {
        loading.value = false;
        if (filterFetchPending.value) {
            filterFetchPending.value = false;
            await nextTick();
            const el =
                (lastFocusedFilterEl.value &&
                    document.contains(lastFocusedFilterEl.value)
                    ? lastFocusedFilterEl.value
                    : null) ||
                (lastFocusedFilterId.value
                    ? document.getElementById(lastFocusedFilterId.value)
                    : null);
            if (el && typeof el.focus === 'function') {
                el.focus();
                if (typeof el.setSelectionRange === 'function') {
                    const len = el.value?.length ?? 0;
                    el.setSelectionRange(len, len);
                }
            }
        }
    }
};

const fetchColumnArrangeData = async () => {
    // Disabled to avoid 404 since common.rearrange-column is missing
};

const onPage = (event) => {
    page.value = event.page + 1;
    size.value = event.rows;
    fetchData();
};

const onSort = (event) => {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;
    fetchData();
};

const onFilterChange = () => {
    page.value = 1;
    filterFetchPending.value = true;
    emit('filters-change', getCurrentFilters());
    if (filterDebounce.value) {
        clearTimeout(filterDebounce.value);
    }
    filterDebounce.value = setTimeout(() => {
        fetchData();
    }, 400);
};

function exportCSV() {
    dt.value.exportCSV();
}

// used for dynamic/async componets
const resolveParams = (data, param, filterData = null) => {
    const result = {};
    Object.keys(param).forEach((key) => {
        if (param[key].is_query_data == 'yes') {
            if (param[key].val === 'whole_row') {
                result[param[key].nm] = data;
            } else if (param[key].val.includes('.')) {
                let current = data;
                const parts = param[key].val.split('.');
                for (let i = 0; i < parts.length; i++) {
                    current = current?.[parts[i]];
                }
                result[param[key].nm] = current;
            } else {
                result[param[key].nm] = data[param[key].val];
            }
        } else if (param[key].is_filter_data == 'yes') {
            result[param[key].nm] = filterData?.[param[key].val];
        } else {
            // not match then consider as string param
            result[param[key].nm] = param[key].val;
        }
    });
    return result;
};

const resolveEmit = (method, filter) => {
    let result = {};

    if (method.emit) {
        result = '@' + method.emit + '=' + filters[filter];
    }
    return result;
};

const handFilterEmit = (event) => {
    fetchData();
};

//filters[column.filterNm]

const columns = ref(props.allColumns);
watch(
    () => props.allColumns,
    (newVal) => {
        columns.value = newVal;
        applyFooterTotals(products.value);
    },
);
watch(
    () => props.footerTotals,
    () => {
        applyFooterTotals(products.value);
    },
);

const reArrangeColumn = () => {
    //columns_rearrange.value = columns_rearrange.value ? Object.fromEntries(Object.entries(columns_rearrange.value).sort(([, a], [, b]) => a.localeCompare(b))) : [];
    columns.value = arrangeColumns(props.allColumns);
};

// Function to arrange columns based on the specified order
const arrangeColumns = (columns) => {
    const noColumn = columns.find((column) => column.field === 'no');
    const otherColumns = columns.filter((column) => column.field !== 'no');
    const orderedColumns = Object.entries(columns_rearrange.value)
        .map(([header, order]) => {
            let othColumn = otherColumns.find(
                (column) => column.header === header,
            );
            if (!othColumn) return undefined;
            return {
                ...othColumn,
                visible: true, // add order from entries
            };
        })
        .filter((column) => column !== undefined);
    const result = orderedColumns.length > 0 ? orderedColumns : otherColumns;
    return noColumn ? [noColumn, ...result] : result;
};

const normalizeFooterConfig = () => {
    if (!props.footerTotals) return [];
    if (typeof props.footerTotals === 'string') {
        return [{ field: props.footerTotals, decimals: 3 }];
    }
    if (Array.isArray(props.footerTotals)) {
        return props.footerTotals
            .map((item) => {
                if (typeof item === 'string') {
                    return { field: item, decimals: 3 };
                }
                return {
                    field: item?.field,
                    decimals:
                        typeof item?.decimals === 'number' ? item.decimals : 3,
                };
            })
            .filter((item) => item.field);
    }
    return [];
};

const applyFooterTotals = (rows) => {
    const config = normalizeFooterConfig();
    if (!config.length || !Array.isArray(columns.value)) {
        return;
    }
    const list = Array.isArray(rows) ? rows : [];
    const sumCommaSeparated = (field) =>
        list.reduce((acc, r) => {
            const value = r?.[field];
            if (!value) return acc;
            if (typeof value === 'string' && value.includes(',')) {
                const values = value
                    .split(',')
                    .map((v) => parseFloat(v.trim()) || 0);
                return acc + values.reduce((s, val) => s + val, 0);
            }
            return acc + (Number(value) || 0);
        }, 0);

    const noCol = columns.value.find((c) => c.field === 'no');
    if (noCol) noCol.footer = 'Total';

    config.forEach(({ field, decimals }) => {
        const col = columns.value.find((c) => c.field === field);
        if (!col) return;
        const total = sumCommaSeparated(field);
        col.footer = Number(total).toFixed(decimals);
    });
};

const getCurrentFilters = () => {
    return {
        ...(props.route_param || {}),
        ...sanitizeFilters(filters.value),
    };
};

onMounted(() => {
    if (props.allColumns) {
        const initialFilters = {};
        props.allColumns.forEach(col => {
            if (col.filterDefault !== undefined && col.filterNm) {
                initialFilters[col.filterNm] = col.filterDefault;
            }
        });
        filters.value = {
            ...filters.value,
            ...initialFilters,
            ...sanitizeFilters(props.initialFilters),
        };
    }
    // fetchColumnArrangeData();
    fetchData('mounted');
    window.addEventListener('keydown', handleKeydown);
});
onBeforeUnmount(() => {
    if (filterDebounce.value) {
        clearTimeout(filterDebounce.value);
    }
    window.removeEventListener('keydown', handleKeydown);
});
const handleKeydown = (event) => {
    if (
        event.ctrlKey &&
        event.shiftKey &&
        event.key === 'A' &&
        props.moduleNm == 'crm_tour'
    ) {
        toggleState.value = !toggleState.value;
        filters.value = {
            close_tour_show: toggleState.value ? 'yes' : 'no',
        };
        fetchData();
    } else if (event.altKey && event.key === 'a') {
        emit('shortcut-key');
    }
};
const getActionMenu = (data) => {
    return props.menuItems
        ? props.menuItems
              .filter(
                  (item) =>
                      (!item.permission ||
                          (props.permissions[item.permission] || item.permission_name)) &&
                      (!item.visible ||
                          (typeof item.visible === 'function'
                              ? item.visible(data)
                              : item.visible)),
              )
                .map((item) => {
                  const menuItem = {
                     label:
                          typeof item.label === 'function'
                              ? item.label(data)
                              : item.label,
                      icon: item.icon,
                  };

                  const menuUrl = resolveMenuItemUrl(item, data);
                  if (menuUrl) {
                      menuItem.url = menuUrl;
                  }
                  if (item.target) {
                      menuItem.target =
                          typeof item.target === 'function'
                              ? item.target(data)
                              : item.target;
                  }

                  if (item.items) {
                      const subItems =
                          typeof item.items === 'function'
                              ? item.items(data)
                              : item.items;
                      menuItem.items = (subItems || [])
                          .filter(
                              (subItem) =>
                                  !subItem.visible ||
                                  (typeof subItem.visible === 'function'
                                      ? subItem.visible(data)
                                      : subItem.visible),
                          )
                          .map((subItem) => ({
                             label:
                                  typeof subItem.label === 'function'
                                      ? subItem.label(data)
                                      : subItem.label,
                              icon: subItem.icon,
                              url:
                                  typeof subItem.url === 'function'
                                      ? subItem.url(data)
                                      : subItem.url,
                              target:
                                  typeof subItem.target === 'function'
                                      ? subItem.target(data)
                                      : subItem.target,
                              command: !resolveMenuItemUrl(subItem, data) && subItem.action
                                  ? (event) => subItem.action(data, event)
                                  : undefined,
                          }));
                  } else {
                      if (!menuUrl && item.action) {
                          menuItem.command = (event) => item.action(data, event);
                      }
                  }

                  return menuItem;
              })
        : null;
};

const getCheckboxMenu = (data) => {
    return props.checkboxMenu.map((item) => {
        const menuItem = {
            label: item.label,
            icon: item.icon,
        };

        const menuUrl = resolveMenuItemUrl(item, data);
        if (menuUrl) {
            menuItem.url = menuUrl;
        }
        if (item.target) {
            menuItem.target =
                typeof item.target === 'function' ? item.target(data) : item.target;
        }

        if (item.items) {
            menuItem.items = item.items.map((subItem) => ({
                label: subItem.label,
                icon: subItem.icon,
                url:
                    typeof subItem.url === 'function'
                        ? subItem.url(data)
                        : subItem.url,
                target:
                    typeof subItem.target === 'function'
                        ? subItem.target(data)
                        : subItem.target,
                command: !resolveMenuItemUrl(subItem, data) && subItem.action
                    ? (event) => subItem.action(data, event)
                    : undefined,
            }));
        } else {
            if (!menuUrl && item.action) {
                menuItem.command = (event) => item.action(data, event);
            }
        }

        return menuItem;
    });
};

const setColumnMenuRef = (field, el) => {
    if (!field || !el) {
        return;
    }
    columnMenuRefs.value[field] = el;
};

const getRowKey = (row) => row?.id;

const getSelectedIdsForField = (field) => {
    return columnSelections.value[field] || [];
};

const getSelectedRowsForField = (field) => {
    const selectedIds = getSelectedIdsForField(field);
    return (products.value || []).filter((row) => selectedIds.includes(row.id));
};

const getVisibleSelectedCountForField = (field) => {
    return getSelectedRowsForField(field).length;
};

const isFieldRowSelected = (field, row) => {
    const rowKey = getRowKey(row);
    if (rowKey === undefined || rowKey === null) {
        return false;
    }
    return getSelectedIdsForField(field).includes(rowKey);
};

const selectAllForField = (field, checked) => {
    const column = columns.value.find((c) => c.field === field);
    const allRowIds = (products.value || [])
        .filter((row) => {
            if (typeof column?.checkboxVisible === 'function') {
                return column.checkboxVisible(row);
            }
            return true;
        })
        .map((row) => getRowKey(row))
        .filter((id) => id !== undefined && id !== null);
    columnSelections.value[field] = checked ? allRowIds : [];
};

const toggleFieldRowSelection = (field, row, checked) => {
    const rowKey = getRowKey(row);
    if (rowKey === undefined || rowKey === null) {
        return;
    }

    const existing = getSelectedIdsForField(field);

    if (checked) {
        if (!existing.includes(rowKey)) {
            columnSelections.value[field] = [...existing, rowKey];
        }
        return;
    }

    columnSelections.value[field] = existing.filter((id) => id !== rowKey);
};

const isAllSelectedForField = (field) => {
    const column = columns.value.find((c) => c.field === field);
    const eligibleRows = (products.value || []).filter((row) => {
        if (typeof column?.checkboxVisible === 'function') {
            return column.checkboxVisible(row);
        }
        return true;
    });
    if (!eligibleRows.length) {
        return false;
    }
    return getVisibleSelectedCountForField(field) === eligibleRows.length;
};

const isPartialSelectedForField = (field) => {
    const column = columns.value.find((c) => c.field === field);
    const eligibleCount = (products.value || []).filter((row) => {
        if (typeof column?.checkboxVisible === 'function') {
            return column.checkboxVisible(row);
        }
        return true;
    }).length;
    const visibleSelectedCount = getVisibleSelectedCountForField(field);
    return visibleSelectedCount > 0 && visibleSelectedCount < eligibleCount;
};

const getColumnCheckboxMenu = (column) => {
    const selectedRows = getSelectedRowsForField(column.field);
    if (!selectedRows.length) {
        return [];
    }

    const menuItems = column.checkboxMenu || [];
    return menuItems.map((item) => ({
        label: item.label,
        icon: item.icon,
        command: item.action ? () => item.action(selectedRows) : undefined,
    }));
};

const toggleColumnMenu = (event, field) => {
    if (!field || !columnMenuRefs.value[field]) {
        return;
    }
    columnMenuRefs.value[field].toggle(event);
};

const contextMenuVisible = ref(false); // To control the visibility of the context menu
const menu = ref(); // used for context menu
const checkboxMenuRef = ref(); // used for checkbox menu
const onRightClick = (event, user) => {
    if (isModifierPressed(event) && props.menuItems?.length) {
        const editItem = props.menuItems.find(
            (item) => item.type === 'edit' && isMenuItemAllowed(item, user),
        );
        if (editItem && triggerEditShortcut(editItem, user, event)) {
            event?.preventDefault?.();
            event?.stopPropagation?.();
            return;
        }
    }

    if (props.menuItems && props.menuItems.length > 0) {
        contextMenuVisible.value = user;
        menu.value.show(event);
    }
};

const getCheckRelationValue = (obj, path) => {
    if (!obj || !path) {
        return '';
    }

    const parts = path.split('.');
    let current = obj;

    for (let i = 0; i < parts.length; i++) {
        if (Array.isArray(current)) {
            const restPath = parts.slice(i).join('.');
            const values = current
                .map((item) => getCheckRelationValue(item, restPath))
                .flat()
                .filter(
                    (value) =>
                        value !== null && value !== undefined && value !== '',
                );

            return [...new Set(values)].join(', ');
        }

        current = current?.[parts[i]];
        if (current === undefined || current === null) {
            return '';
        }
    }

    if (Array.isArray(current)) {
        const values = current
            .map((value) =>
                value === null || value === undefined ? '' : value,
            )
            .filter((value) => value !== '');
        return [...new Set(values)].join(', ');
    }

    return current ?? '';
};
defineExpose({
    fetchData,
    getCurrentFilters,
    exportCSV,
    setColumnFooter: (field, value) => {
        if (!field) return;
        const col = columns.value.find((c) => c.field === field);
        if (col) col.footer = value;
    },
    clearColumnSelection: (field) => {
        if (field) {
            columnSelections.value[field] = [];
        } else {
            columnSelections.value = {};
        }
    },
});
const filtersVisible = ref(true);
const toggleFilters = () => {
    filtersVisible.value = !filtersVisible.value;
};

const setFocusedFilter = (filterNm, event) => {
    lastFocusedFilterId.value = filterNm ? `filter_${filterNm}` : null;
    lastFocusedFilterEl.value = event?.target || null;
};

const rowClassResolver = (data) => {
    if (typeof props.rowClassFn === 'function') {
        return props.rowClassFn(data);
    }

    return '';
};
const getRowNo = (index) => (page.value - 1) * size.value + (index + 1);
const isRestrictedValue = (value) => {
    if (value === true || value === 1) return true;
    if (typeof value === 'string') {
        const normalized = value.trim().toLowerCase();
        return normalized === '1' || normalized === 'yes' || normalized === 'true';
    }

    return false;
};

const handleColumnCheckboxChange = async (column, row, checked) => {
    const trueValue = column.trueValue ?? 'yes';
    const falseValue = column.falseValue ?? 'no';
    const prevValue = row?.[column.field];

    // optimistic update
    row[column.field] = checked ? trueValue : falseValue;

    if (typeof column.checkboxAction !== 'function') {
        // fallback: just emit and refresh
        fetchData();
        emit('body-emit-value', {
            name: column.field,
            value: row[column.field],
            row_id: row?.id,
        });
        return;
    }

    try {
        await column.checkboxAction(row, checked);
        fetchData();
        emit('body-emit-value', {
            name: column.field,
            value: row[column.field],
            row_id: row?.id,
        });
    } catch (error) {
        // revert
        row[column.field] = prevValue;
        console.error('Error updating checkbox value:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to update value',
            life: 3000,
        });
    }
};
</script>

<template>
    <div>
        <div
            class="card !rounded-xl !pb-0 !pl-2 !pt-2"
            v-if="!firstLoading"
            :class="{ 'horizontal-menu': isHorizontal }"
        >
            <div>
            <DataTable
                ref="dt"
                :selectionMode="props.checkboxMenu ? null : 'single'"
                filterDisplay="row"
                v-model:selection="selectedProducts"
                :value="products"
                :lazy="true"
                dataKey="id"
                :paginator="true"
                :rows="size"
                :totalRecords="totalRecords"
                :filters="primevueFilters"
                :rowClass="rowClassResolver"
                size="small"
                :loading="loading"
                @page="onPage"
                @sort="onSort"
                @row-contextmenu="onRightClick($event.originalEvent, $event.data)"
                v-model:contextMenuSelection="contextSelect"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[10, 25, 50, 100]"
                currentPageReportTemplate="{first} - {last} &nbsp of &nbsp{totalRecords}  &nbsp &nbsp"
                scrollable
                scrollHeight="100vh"
            >
                <template #loading>
                    <slot name="loader">
                        <div class="flex justify-center items-center py-8">
                            <i class="pi pi-spin pi-spinner" style="font-size: 5rem; color: var(--p-primary-color);"></i>
                        </div>
                    </slot>
                </template>
                <ColumnGroup v-if="shouldRenderColumnGroup" type="header">
                    <Row
                        v-for="(groupRow, rowIndex) in props.columnGroups"
                        :key="'row-' + rowIndex"
                    >
                        <template
                            v-for="(col, colIndex) in groupRow"
                            :key="'col-' + rowIndex + '-' + colIndex"
                        >
                            <Column
                                :colspan="col.colspan"
                                :rowspan="col.rowspan"
                                :header="col.filterToggle ? null : col.header"
                                :field="col.field"
                                :sortable="col.sortable || false"
                            >
                                <template #header v-if="col.filterToggle">
                                    <div class="flex items-center justify-center gap-2">
                                        <i
                                            :class="[
                                                'pi cursor-pointer text-xs text-slate-400 transition-colors hover:text-indigo-500',
                                                filtersVisible
                                                    ? 'pi-filter-slash'
                                                    : 'pi-filter',
                                            ]"
                                            @click.stop="toggleFilters"
                                            v-tooltip.bottom="'Filters'"
                                        ></i>
                                        <span v-if="col.header">{{ col.header }}</span>
                                    </div>
                                </template>
                            </Column>
                        </template>
                    </Row>
                </ColumnGroup>
                <slot
                    name="columnGroup"
                    :toggleFilters="toggleFilters"
                    :filtersVisible="filtersVisible"
                    :columns="columns"
                ></slot>
                <Column
                    v-if="checkboxMenu"
                    selectionMode="multiple"
                    style="width: 3rem"
                    :exportable="false"
                    rendered="false"
                >
                    <template #header>
                        <Button
                            v-if="selectedProducts && selectedProducts.length"
                            icon="pi pi-fw pi-list layout-menuitem-icon"
                            class="p-button-text p-button-plain p-button-rounded"
                            @click.prevent="checkboxMenuRef.toggle($event)"
                            aria-haspopup="true"
                            aria-controls="overlay_checkbox_menu"
                        />
                        <Menu
                            v-if="selectedProducts && selectedProducts.length"
                            ref="checkboxMenuRef"
                            id="overlay_checkbox_menu"
                            :model="getCheckboxMenu(selectedProducts)"
                            :popup="true"
                        />
                    </template>
                </Column>

                <template v-for="column in columns" :key="column.field">
                    <Column
                        v-if="column.visible"
                        :field="column.field"
                        :header="
                            column.selectionCheckbox === true
                                ? ''
                                : column.header
                        "
                        :sortable="column.sortable"
                        :style="{ 'min-width': column.width }"
                    >
                        <template
                            #header
                            v-if="column.field === 'no' && !hasColumnGroup"
                        >
                            <div>
                                <i
                                    :class="[
                                        'pi cursor-pointer text-xs text-slate-400 transition-colors hover:text-indigo-500',
                                        filtersVisible
                                            ? 'pi-filter-slash'
                                            : 'pi-filter',
                                    ]"
                                    @click.stop="toggleFilters"
                                    v-tooltip.bottom="'Filters'"
                                ></i>
                            </div>
                        </template>
                        <template
                            #header
                            v-else-if="column.selectionCheckbox === true"
                        >
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    v-if="column.headerCheckbox !== false"
                                    :binary="true"
                                    :modelValue="
                                        isAllSelectedForField(column.field)
                                    "
                                    :indeterminate="
                                        isPartialSelectedForField(column.field)
                                    "
                                    @update:modelValue="
                                        (val) =>
                                            selectAllForField(column.field, val)
                                    "
                                />
                                <span>{{ column.header }}</span>
                                <Button
                                    v-if="
                                        getVisibleSelectedCountForField(
                                            column.field,
                                        )
                                    "
                                    icon="pi pi-fw pi-list layout-menuitem-icon"
                                    class="p-button-text p-button-plain p-button-rounded"
                                    @click.prevent="
                                        toggleColumnMenu($event, column.field)
                                    "
                                    aria-haspopup="true"
                                    :aria-controls="`overlay_column_menu_${column.field}`"
                                />
                                <Menu
                                    v-if="
                                        getVisibleSelectedCountForField(
                                            column.field,
                                        )
                                    "
                                    :ref="
                                        (el) =>
                                            setColumnMenuRef(column.field, el)
                                    "
                                    :id="`overlay_column_menu_${column.field}`"
                                    :model="getColumnCheckboxMenu(column)"
                                    :popup="true"
                                />
                            </div>
                        </template>
                        <template #filtericon></template>
                        <template
                            #filter="{}"
                            v-if="column.filterType && filtersVisible"
                        >
                            <div
                                class="filter-content"
                                v-if="column.filterType === 'text'"
                            >
                                <InputText
                                    type="text"
                                    v-model="filters[column.filterNm]"
                                    @input="onFilterChange()"
                                    @focus="setFocusedFilter(column.filterNm, $event)"
                                    :id="`filter_${column.filterNm}`"
                                    :placeholder="'Search by ' + column.header"
                                />
                            </div>
                            <div
                                class="filter-content"
                                v-if="column.filterType === 'number'"
                            >
                                <InputText
                                    type="number"
                                    v-model="filters[column.filterNm]"
                                    @input="onFilterChange()"
                                    @focus="setFocusedFilter(column.filterNm, $event)"
                                    :id="`filter_${column.filterNm}`"
                                    :placeholder="'Search by ' + column.header"
                                    class="[&::-moz-appearance:textfield] appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                />
                            </div>
                            <div
                                class="filter-content"
                                v-else-if="column.filterType === 'select'"
                            >
                                <Select
                                    v-model="filters[column.filterNm]"
                                    :options="column.filterOptions || column.options"
                                    :optionLabel="column.optionLabel || 'label'"
                                    :optionValue="column.optionValue || 'value'"
                                    :placeholder="
                                        column.placeholder || 'Select Status'
                                    "
                                    fluid
                                    @change="onFilterChange()"
                                />
                            </div>
                            <div
                                class="filter-content"
                                v-else-if="column.filterType === 'date'"
                            >
                                <DatePicker
                                    v-model="filters[column.filterNm]"
                                    showIcon
                                    dateFormat="yy-mm-dd"
                                    class="w-full"
                                    @update:modelValue="onFilterChange()"
                                />
                            </div>
                            <div
                                class="filter-content"
                                v-else-if="column.filter_components"
                                :style="{ width: column.width }"
                            >
                                <component
                                    :is="column.filter_components.name"
                                    @[column.filter_components.emit]="
                                        (value) => {
                                            filters[column.filterNm] = value;
                                            onFilterChange();
                                        }
                                    "
                                    v-bind="
                                        resolveFilterParams(column)
                                    "
                                />
                            </div>
                        </template>
                        <template #body="slotProps">
                            <div v-if="column.field === 'no'">
                                <span class="no-column-cell">
                                    <span>{{ getRowNo(slotProps.index) }}</span>
                                    <i
                                        v-if="
                                            column.restrictedIconField &&
                                            isRestrictedValue(
                                                slotProps.data?.[
                                                    column.restrictedIconField
                                                ],
                                            )
                                        "
                                        class="pi pi-lock no-column-lock-icon"
                                        v-tooltip.top="
                                            column.restrictedIconTooltip ||
                                            'Restricted'
                                        "
                                    ></i>
                                    
                                </span>
                            </div>
                            <div v-else-if="column.checkbox === true">
                                <Checkbox
                                    :binary="true"
                                    :modelValue="
                                        (slotProps.data?.[column.field] ??
                                            column.falseValue ??
                                            'no') ===
                                        (column.trueValue ?? 'yes')
                                    "
                                    @update:modelValue="
                                        (val) =>
                                            handleColumnCheckboxChange(
                                                column,
                                                slotProps.data,
                                                val,
                                            )
                                    "
                                />
                            </div>
                            <div v-else-if="column.selectionCheckbox === true">
                                <Checkbox
                                    v-if="
                                        typeof column.checkboxVisible !== 'function' ||
                                        column.checkboxVisible(slotProps.data)
                                    "
                                    :binary="true"
                                    :modelValue="
                                        isFieldRowSelected(
                                            column.field,
                                            slotProps.data,
                                        )
                                    "
                                    @update:modelValue="
                                        (val) =>
                                            toggleFieldRowSelection(
                                                column.field,
                                                slotProps.data,
                                                val,
                                            )
                                    "
                                />
                                <span
                                    v-else-if="
                                        typeof column.customRender === 'function'
                                    "
                                    v-html="
                                        column.customRender(
                                            slotProps.data,
                                            column,
                                        )
                                    "
                                ></span>
                            </div>
                            <div v-else-if="column.body_components">
                                <component
                                    :is="{ ...column.body_components.name }"
                                    v-bind="
                                        resolveParams(
                                            slotProps.data,
                                            column.body_components.param,
                                        )
                                    "
                                    @[column.body_components.emit]="
                                        (value) => {
                                            fetchData();

                                            emit('body-emit-value', {
                                                name: column.field,
                                                value: value,
                                                row_id: slotProps.data.id,
                                            });
                                        }
                                    "
                                />
                            </div>
                            <div
                                v-else-if="
                                    typeof column.customRender === 'function'
                                "
                            >
                                <span
                                    v-html="
                                        column.customRender(
                                            slotProps.data,
                                            column,
                                        )
                                    "
                                ></span>
                            </div>
                            <div v-else-if="column.isHtml" class="html-content">
                                <span
                                    v-html="slotProps.data[column.field]"
                                ></span>
                            </div>
                            <div v-else>
                                {{
                                    getCheckRelationValue(
                                        slotProps.data,
                                        column.field,
                                    )
                                }}
                            </div>
                        </template>
                        <template #footer v-if="column.footer !== undefined">
                            <div class="font-bold">
                                {{ column.footer }}
                            </div>
                        </template>
                    </Column>
                </template>
                <template #empty>
                    <div class="p-3 text-center">No Data Available</div>
                </template>
                <Column
                    v-if="showActionButton !== false"
                    header="Action"
                    style="width: 3rem"
                    :exportable="false"
                    footer=""
                >
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-fw pi-list layout-menuitem-icon"
                            class="p-button-text p-button-plain p-button-rounded"
                            @click.prevent="
                                $refs[`menu_${slotProps.data.id}`].toggle(
                                    $event,
                                )
                            "
                            aria-haspopup="true"
                            :aria-controls="`menu_${slotProps.data.id}`"
                        />
                        <TieredMenu
                            :ref="`menu_${slotProps.data.id}`"
                            :model="getActionMenu(slotProps.data)"
                            :popup="true"
                            :id="`menu_${slotProps.data.id}`"
                        />
                    </template>
                </Column>
            </DataTable>
            <ContextMenu
                ref="menu"
                :model="getActionMenu(contextSelect)"
                @hide="contextMenuVisible = false"
            />
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.p-datatable-thead > tr > th) {
    transition: all 0.3s ease-in-out;
    overflow: hidden;
    padding: 0.5rem !important;
}

:deep(.p-datatable-thead > tr > th .p-column-filter) {
    width: 100%;
}

:deep(.p-datatable-thead > tr > th .p-inputtext) {
    width: 100%;
    transition: all 0.3s ease-in-out;
}

:deep(.p-datatable-thead > tr > th.p-filter-column) {
    transition: all 0.3s ease-in-out;
    padding: 0.5rem !important;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    transition: all 0.3s ease-in-out;
    overflow: hidden;
}

:deep(.p-datatable .p-datatable-thead > tr > th.p-filter-column) {
    padding: 0.5rem !important;
    transition: all 0.3s ease-in-out;
}

:deep(.p-datatable .p-datatable-thead > tr > th .p-inputtext) {
    width: 100%;
    transition: all 0.3s ease-in-out;
}
:deep(.p-datatable .p-datatable-tbody > tr > td) {
    color: #475569 !important;
    overflow: hidden;
    text-overflow: ellipsis;
}

.no-column-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.no-column-lock-icon {
    font-size: 0.9rem;
    color: #94a3b8;
    cursor: default;
    flex-shrink: 0;
}

:deep(.p-datatable .p-datatable-tbody > tr) {
    border-color: #f1f5f9 !important;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover > td) {
    color: #334155 !important;
}
@media screen and (min-width: 320px) and (max-width: 767px) {
    .p-datatable-scrollable > :deep(.p-datatable-table-container) {
        height: calc(100vh - 18rem) !important;
    }
}
@media screen and (min-width: 1920px)  {
    .horizontal-menu .p-datatable-scrollable > :deep(.p-datatable-table-container) {
        height: calc(100vh - 18rem) !important;
    }
}

@media screen and (min-width: 1920px)  {
    .p-datatable-scrollable > :deep(.p-datatable-table-container) {
        height: calc(100vh - 16rem) !important;
    }
}
@media screen and (min-width: 768px) and (max-width: 1920px) {
    .p-datatable-scrollable > :deep(.p-datatable-table-container) {
        height: calc(100vh - 15.3rem) !important;
    }
}
@media screen and (min-width: 768px) and (max-width: 1920px) {
    .horizontal-menu
        .p-datatable-scrollable
        > :deep(.p-datatable-table-container) {
        height: calc(100vh - 18rem) !important;
    }
}
</style>
