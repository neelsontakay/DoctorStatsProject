export const ANALYSIS_STATUSES = ['pending', 'processing', 'completed', 'failed'] as const
export type AnalysisStatus = (typeof ANALYSIS_STATUSES)[number]

export const DATA_TYPES = [
  'categorical',
  'numerical',
  'date',
  'text',
  'boolean',
] as const
export type DataType = (typeof DATA_TYPES)[number]

export const VARIABLE_ROLES = [
  'independent',
  'dependent',
  'control',
  'identifier',
  'excluded',
] as const
export type VariableRole = (typeof VARIABLE_ROLES)[number]

export const ORG_ROLES = ['admin', 'analyst', 'viewer'] as const
export type OrgRole = (typeof ORG_ROLES)[number]

export const ACCESS_SCOPES = ['all_members', 'specific_members', 'private'] as const
export type AccessScope = (typeof ACCESS_SCOPES)[number]

export const MEMBER_STATUSES = ['active', 'pending', 'inactive'] as const
export type MemberStatus = (typeof MEMBER_STATUSES)[number]

export function formatDataType(type: DataType): string {
  return type.charAt(0).toUpperCase() + type.slice(1)
}

export function formatVariableRole(role: VariableRole): string {
  const labels: Record<VariableRole, string> = {
    independent: 'Independent',
    dependent: 'Dependent',
    control: 'Control',
    identifier: 'Identifier',
    excluded: 'Excluded',
  }
  return labels[role]
}

export function formatOrgRole(role: OrgRole): string {
  return role.charAt(0).toUpperCase() + role.slice(1)
}
