public class Segment {
	public static void main(String[] args){
		private int x;
		private int y;
		public Segment(int extX, int extY){
			this.x =extX;
			this.y=extY;
		
		}
		public int longueur(){
			if(x>y){
				return x-y;
			}
			else{
				return y-x;
			}
		}
		public String toString(){
			return "Segment ["+x+","+y+"]";
		}
	}
}

