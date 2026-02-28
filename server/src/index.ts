import express, { Request, Response } from 'express';
import mongoose from 'mongoose';
import cors from 'cors';

const app = express();
app.use(cors());
app.use(express.json());

mongoose.connect(process.env.MONGO_URI!)
  .then(() => console.log('¡Conectado a Mongo local!'))
  .catch(err => console.error('Error:', err));

app.get('/', (req: Request, res: Response) => {
  res.send('API del Gimnasio con TypeScript funcionando');
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Servidor TS corriendo en el puerto ${PORT}`);
});