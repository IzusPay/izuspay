import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Loader2 } from "lucide-react"

interface DataTableProps<T> {
  columns: {
    header: string
    accessorKey?: keyof T
    cell?: (item: T) => React.ReactNode
    className?: string
  }[]
  data: T[]
  isLoading?: boolean
  emptyMessage?: string
}

export function DataTable<T extends { id?: string | number }>({
  columns,
  data,
  isLoading,
  emptyMessage = "Nenhum registro encontrado.",
}: DataTableProps<T>) {
  if (isLoading) {
    return (
      <div className="flex h-48 items-center justify-center border rounded-md bg-background">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  return (
    <div className="rounded-md border bg-background">
      <Table>
        <TableHeader>
          <TableRow>
            {columns.map((col, index) => (
              <TableHead key={index} className={col.className}>
                {col.header}
              </TableHead>
            ))}
          </TableRow>
        </TableHeader>
        <TableBody>
          {data.length > 0 ? (
            data.map((row, rowIndex) => (
              <TableRow key={row.id || rowIndex}>
                {columns.map((col, colIndex) => (
                  <TableCell key={colIndex} className={col.className}>
                    {col.cell
                      ? col.cell(row)
                      : col.accessorKey
                      ? (row[col.accessorKey] as React.ReactNode)
                      : null}
                  </TableCell>
                ))}
              </TableRow>
            ))
          ) : (
            <TableRow>
              <TableCell colSpan={columns.length} className="h-24 text-center text-muted-foreground">
                {emptyMessage}
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </div>
  )
}
