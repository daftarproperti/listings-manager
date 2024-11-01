import { router } from '@inertiajs/react'
import { Typography } from '@material-tailwind/react'

import Table from '@/Components/Table'
import { type DPUser, type OptionWithKey } from '@/types'

const MemberTable = ({
  headers,
  members,
}: {
  headers: OptionWithKey[]
  members: DPUser[]
}) => {
  return (
    <Table>
      <Table.Header>
        {headers.map((head, index) => (
          <Table.HeaderItem key={index}>{head.label}</Table.HeaderItem>
        ))}
      </Table.Header>
      <Table.Body>
        {members?.map((member, index) => (
          <tr
            key={index}
            className="cursor-pointer bg-white"
            onClick={(event) => {
              if (event.metaKey || event.ctrlKey) {
                window.open(`/admin/members/${member.id}`, '_blank')
              } else {
                router.get(`/admin/members/${member.id}`)
              }
            }}
          >
            {headers.map((head, index) => {
              switch (head.key) {
                case 'name':
                  return (
                    <Table.BodyItem key={index}>
                      <div className="flex items-center gap-3">
                        <img
                          className="inline-block size-10 rounded-full ring-2 ring-white"
                          src={
                            member?.picture ??
                            '/images/logo_icon.svg' /* TODO: Wire picture URL here */
                          }
                          alt={member.name}
                        />
                        <div className="space-y-0.5 truncate">
                          <Typography className="text-neutral-600">
                            {member?.name ?? ''}
                          </Typography>
                          <Typography
                            variant="small"
                            className="truncate leading-none text-blue-gray-300"
                          >
                            #{member.userId}
                          </Typography>
                        </div>
                      </div>
                    </Table.BodyItem>
                  )
                case 'isDelegateEligible':
                  return (
                    <Table.BodyItem key={index}>
                      {member.isDelegateEligible ? 'Ya' : '-'}
                    </Table.BodyItem>
                  )
                default:
                  return (
                    <Table.BodyItem key={index}>
                      {member[head.key as keyof DPUser] ?? '-'}
                    </Table.BodyItem>
                  )
              }
            })}
          </tr>
        ))}
        {members.length === 0 && (
          <tr>
            <Table.BodyItem
              colSpan={headers.length}
              className="text-center text-sm"
            >
              No data
            </Table.BodyItem>
          </tr>
        )}
      </Table.Body>
    </Table>
  )
}

export default MemberTable
