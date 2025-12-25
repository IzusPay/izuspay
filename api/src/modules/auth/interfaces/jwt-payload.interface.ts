export interface JwtPayload {
  username: string;
  sub: string;
  companyId?: string;
  role?: string;
}