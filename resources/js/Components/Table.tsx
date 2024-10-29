import {
  type HTMLAttributes,
  type TableHTMLAttributes,
  type TdHTMLAttributes,
  type ThHTMLAttributes,
} from 'react'

const Table = ({
  children,
  className = '',
  ...props
}: TableHTMLAttributes<HTMLTableElement>): JSX.Element => {
  return (
    <div className="full-size overflow-auto">
      <table
        className={`w-full min-w-max table-auto text-left md:table-fixed ${className}`}
        {...props}
      >
        {children}
      </table>
    </div>
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
  className = '',
  ...props
}: ThHTMLAttributes<HTMLTableHeaderCellElement>): JSX.Element => {
  return (
    <th
      className={`border-y border-blue-gray-100 bg-stone-50 p-4 ${className}`}
      {...props}
    >
      {typeof children === 'string' ? (
        <p className="truncate font-normal leading-none text-neutral-500">
          {children}
        </p>
      ) : (
        children
      )}
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
  className = '',
  ...props
}: TdHTMLAttributes<HTMLTableDataCellElement>): JSX.Element => {
  return (
    <td className={`border-b p-4 ${className}`} {...props}>
      {typeof children === 'string' ? (
        <p className="truncate font-normal text-neutral-600">{children}</p>
      ) : (
        children
      )}
    </td>
  )
}

Table.Header = TableHeader
Table.HeaderItem = TableHeaderItem
Table.Body = TableBody
Table.BodyItem = TableBodyItem

export default Table
