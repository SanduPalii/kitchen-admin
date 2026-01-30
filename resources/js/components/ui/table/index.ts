// types/TableColumn.ts
export type TableColumn<T = any> = {
    field: keyof T | string;
    header: string;
    sortable?: boolean;
    body?: (row: T) => any;
};
