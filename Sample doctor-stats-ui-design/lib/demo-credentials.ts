export const DEMO_EMAIL = 'test@example.com'
export const DEMO_PASSWORD = 'password'

export function isDemoCredentials(email: string, password: string): boolean {
  return email.trim().toLowerCase() === DEMO_EMAIL && password === DEMO_PASSWORD
}
