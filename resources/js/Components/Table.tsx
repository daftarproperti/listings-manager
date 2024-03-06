import React, {
  type HTMLAttributes,
  type TableHTMLAttributes,
  type TdHTMLAttributes,
  type ThHTMLAttributes
} from 'react'

const Table = ({
  children,
  className,
  ...props
}: TableHTMLAttributes<HTMLTableElement>): JSX.Element => {
  return (
        <table
            className={`w-full min-w-max table-auto text-left table-fixed ${className}`}
            {...props}
        >
            {children}
        </table>
  )
}

const TableHeader = ({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLTableSectionElement>): JSX.Element => {
  return (
        <thead className={className} {...props}>
            <tr>{children}</tr>
        </thead>
  )
}

const TableHeaderItem = ({
  children,
  className,
  ...props
}: ThHTMLAttributes<HTMLTableHeaderCellElement>): JSX.Element => {
  return (
        <th
            className={`border-y border-blue-gray-100 bg-stone-50 px-6 py-4 ${className}`}
            {...props}
        >
            <p className="font-normal leading-none text-neutral-500">
                {children}
            </p>
        </th>
  )
}

const TableBody = ({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLTableSectionElement>): JSX.Element => {
  return (
        <tbody className={className} {...props}>
            {children}
        </tbody>
  )
}

const TableBodyItem = ({
  children,
  className,
  ...props
}: TdHTMLAttributes<HTMLTableDataCellElement>): JSX.Element => {
  return (
        <td className={`p-5 border-b ${className}`} {...props}>
            <p className="truncate text-neutral-600 font-normal">{children}</p>
        </td>
  )
}

Table.Header = TableHeader
Table.HeaderItem = TableHeaderItem
Table.Body = TableBody
Table.BodyItem = TableBodyItem

export default Table
